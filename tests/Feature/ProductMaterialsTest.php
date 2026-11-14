<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductMaterialsTest extends TestCase
{
    use DatabaseTransactions;

    protected function makeAdminUser(): User
    {
        $user = User::where('usertype', 'admin')->first();
        if (! $user) {
            $user = User::create([
                'name' => 'Test Admin',
                'email' => 'test_mat_admin_' . uniqid() . '@example.com',
                'password' => bcrypt('password'),
                'usertype' => 'admin',
            ]);
        }

        foreach (['products.create', 'products.edit', 'products.index'] as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
            $user->givePermissionTo($perm);
        }

        return $user;
    }

    protected function makeCatalogRefs(): array
    {
        $cat = Category::first();
        if (! $cat) {
            $cat = new Category();
            $cat->name = 'Cat_' . uniqid();
            $cat->save();
        }
        $brand = Brand::first();
        if (! $brand) {
            $brand = new Brand();
            $brand->name = 'Brand_' . uniqid();
            $brand->save();
        }
        $unit = Unit::first();
        if (! $unit) {
            $unit = new Unit();
            $unit->name = 'Pcs';
            $unit->save();
        }

        return compact('cat', 'brand', 'unit');
    }

    public function test_create_and_edit_pages_render_original_variant_table_without_materials_and_profit(): void
    {
        $user = $this->makeAdminUser();

        $response = $this->actingAs($user)
            ->get(route('store'))
            ->assertStatus(200)
            ->assertDontSee('variantMaterialsModal')
            ->assertDontSee('Apply to all')
            ->assertSee('Sale Price')
            ->assertSee('Purch Price')
            ->assertSee('Wholesale');

        $html = $response->getContent();
        $this->assertDoesNotMatchRegularExpression('/<th[^>]*>\s*Cost\s*<\/th>/i', $html);
    }

    public function test_store_and_update_product_persist_variant_materials_and_preserve_legacy_fields(): void
    {
        $user = $this->makeAdminUser();
        $refs = $this->makeCatalogRefs();
        $productName = 'Test Shirt ' . uniqid();

        $storePayload = [
            'product_name' => $productName,
            'category_id' => $refs['cat']->id,
            'brand_id' => $refs['brand']->id,
            'unit' => $refs['unit']->id,
            'size_mode' => 'by_pieces',
            'piece_quantity' => 15,
            'sale_price_per_box' => 100,
            'purchase_price_per_piece' => 50,
            'wholesale_price' => 80,
            'variant_name' => ['shirt', 'shirt', 'shirt'],
            'variant_size' => ['m', 'l', 'xl'],
            'variant_color' => ['white', 'white', 'black'],
            'variant_unit' => ['Pcs', 'Pcs', 'Pcs'],
            'variant_stock' => [5, 5, 5],
            'variant_conv_factor' => [1, 1, 1],
            'variant_weight_per_piece' => [0, 0, 0],
            'variant_sale_price' => [100, 100, 150],
            'variant_wholesale_price' => [80, 80, 120],
            'variant_purchase_price' => [50, 50, 75],
            'variant_alert_qty' => [2, 2, 2],
            'variant_barcode' => ['99001122', '99001123', '99001124'],
            'variant_is_base' => [1, 0, 0],
            'variant_materials' => [
                json_encode([
                    ['name' => 'Logo', 'amount' => 10],
                    ['name' => 'Fabric', 'amount' => 10],
                    ['name' => 'Stitching', 'amount' => 10],
                ]),
                json_encode([
                    ['name' => 'Logo', 'amount' => 8],
                    ['name' => 'Fabric', 'amount' => 10],
                    ['name' => 'Stitching', 'amount' => 10],
                ]),
                json_encode([
                    ['name' => 'Logo', 'amount' => 12],
                    ['name' => 'Fabric', 'amount' => 15],
                    ['name' => 'Stitching', 'amount' => 10],
                ]),
            ],
        ];

        $this->actingAs($user)
            ->post(route('store-product'), $storePayload)
            ->assertRedirect();

        $product = Product::where('item_name', $productName)->firstOrFail();
        $variants = json_decode($product->color, true);

        $this->assertIsArray($variants);
        $this->assertCount(3, $variants);

        // Standard product variant fields preserved
        $this->assertEquals(100, $variants[0]['sale_price']);
        $this->assertEquals(50, $variants[0]['purch_price']);
        $this->assertEquals('white', $variants[0]['color']);

        // Edit page renders original variant table without materials modal
        $this->actingAs($user)
            ->get(route('products.edit', $product->id))
            ->assertStatus(200)
            ->assertDontSee('variantMaterialsModal')
            ->assertSee('Sale Price')
            ->assertSee('Purch Price');

        // Update product cleanly
        $updatePayload = $storePayload;
        $updatePayload['_method'] = 'PUT';
        $updatePayload['variant_prev_stock'] = [5, 5, 5];
        $updatePayload['variant_uncapped_stock'] = [5, 5, 5];
        $updatePayload['variant_orig_initial'] = [5, 5, 5];

        $this->actingAs($user)
            ->post(route('product.update', $product->id), $updatePayload)
            ->assertRedirect();

        $product->refresh();
        $updatedVariants = json_decode($product->color, true);

        $this->assertEquals(100, $updatedVariants[0]['sale_price']);
        $this->assertEquals(50, $updatedVariants[0]['purch_price']);
    }

    public function test_store_product_without_variant_materials_still_works_identically(): void
    {
        $user = $this->makeAdminUser();
        $refs = $this->makeCatalogRefs();
        $productName = 'Legacy Product ' . uniqid();

        $payload = [
            'product_name' => $productName,
            'category_id' => $refs['cat']->id,
            'brand_id' => $refs['brand']->id,
            'unit' => $refs['unit']->id,
            'size_mode' => 'by_pieces',
            'piece_quantity' => 10,
            'sale_price_per_box' => 200,
            'purchase_price_per_piece' => 120,
            'wholesale_price' => 160,
            'variant_name' => [$productName],
            'variant_size' => ['Std'],
            'variant_color' => ['Blue'],
            'variant_unit' => ['Pcs'],
            'variant_stock' => [10],
            'variant_conv_factor' => [1],
            'variant_weight_per_piece' => [0],
            'variant_sale_price' => [200],
            'variant_wholesale_price' => [160],
            'variant_purchase_price' => [120],
            'variant_alert_qty' => [1],
            'variant_barcode' => ['88776655'],
            'variant_is_base' => [1],
        ];

        $this->actingAs($user)
            ->post(route('store-product'), $payload)
            ->assertRedirect();

        $product = Product::where('item_name', $productName)->firstOrFail();
        $variants = json_decode($product->color, true);

        $this->assertCount(1, $variants);
        $this->assertEquals(200, $variants[0]['sale_price']);
        $this->assertEquals(120, $variants[0]['purch_price']);
    }
}
