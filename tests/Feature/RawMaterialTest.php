<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RawMaterialTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Warehouse $warehouse;
    protected Unit $unit;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::factory()->create([
            'email' => 'admin@admin.com',
        ]);

        $this->warehouse = Warehouse::first() ?? Warehouse::create([
            'warehouse_name' => 'Test Warehouse',
        ]);

        $this->unit = Unit::first() ?? Unit::create([
            'name' => 'Meter',
        ]);

        $this->category = Category::first() ?? Category::create([
            'name' => 'Fabrics',
        ]);
    }

    public function test_can_view_raw_materials_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('raw_materials.index'));
        $response->assertStatus(200);
        $response->assertSee('Raw Materials');
        $response->assertSee('rawMaterialModal');
    }

    public function test_create_redirects_to_index_with_open_modal(): void
    {
        $response = $this->actingAs($this->user)->get(route('raw_materials.create'));
        $response->assertRedirect(route('raw_materials.index', ['open_modal' => 1]));
    }

    public function test_can_create_raw_material_via_modal(): void
    {
        $data = [
            'item_name' => 'Pure Cotton White 60/60',
            'item_code' => 'RM-MODAL-001',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'purchase_price' => 450.00,
            'alert_quantity' => 15,
            'description' => 'Premium test cotton modal',
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->user)->post(route('raw_materials.store'), $data);
        $response->assertRedirect(route('raw_materials.index'));

        $material = Product::where('item_code', 'RM-MODAL-001')->first();
        $this->assertNotNull($material);
        $this->assertEquals(1, $material->is_raw_material);
        $this->assertEquals(1, $material->is_part);
        $this->assertEquals(450.00, (float)$material->purchase_price_per_piece);
        $this->assertEquals(15.00, (float)$material->alert_quantity);
        $this->assertEquals('Premium test cotton modal', $material->meta_description);
        $this->assertEquals(1, $material->is_active);
    }

    public function test_raw_material_appears_in_purchase_ajax_search(): void
    {
        $material = Product::create([
            'creater_id' => $this->user->id,
            'item_name' => 'Special Zippers 8inch',
            'item_code' => 'RM-ZIP-001',
            'purchase_price_per_piece' => 25.00,
            'is_raw_material' => 1,
            'is_part' => 1,
            'is_active' => 1,
            'size_mode' => 'by_pieces',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('products.ajax.search', ['term' => 'Special Zippers']));
        $response->assertStatus(200);

        $json = $response->json();
        $this->assertNotEmpty($json['results']);
        $first = $json['results'][0];
        $this->assertStringContainsString('Special Zippers', $first['text']);
        $this->assertStringContainsString('Raw Material', $first['text']);
    }

    public function test_can_update_raw_material(): void
    {
        $material = Product::create([
            'creater_id' => $this->user->id,
            'item_name' => 'Buttons Navy 14mm',
            'item_code' => 'RM-BTN-001',
            'purchase_price_per_piece' => 2.50,
            'is_raw_material' => 1,
            'is_part' => 1,
            'is_active' => 1,
            'size_mode' => 'by_pieces',
        ]);

        $response = $this->actingAs($this->user)->put(route('raw_materials.update', $material->id), [
            'item_name' => 'Buttons Navy 14mm Updated',
            'item_code' => 'RM-BTN-001',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'purchase_price' => 3.00,
            'alert_quantity' => 100,
            'description' => 'Updated navy buttons',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('raw_materials.index'));

        $material->refresh();
        $this->assertEquals('Buttons Navy 14mm Updated', $material->item_name);
        $this->assertEquals(3.00, (float)$material->purchase_price_per_piece);
    }

    public function test_can_toggle_active_status(): void
    {
        $material = Product::create([
            'creater_id' => $this->user->id,
            'item_name' => 'Lining Silk Black',
            'item_code' => 'RM-LNK-001',
            'purchase_price_per_piece' => 120.00,
            'is_raw_material' => 1,
            'is_part' => 1,
            'is_active' => 1,
            'size_mode' => 'by_pieces',
        ]);

        $response = $this->actingAs($this->user)->postJson(route('raw_materials.toggle_active', $material->id));
        $response->assertStatus(200);
        $response->assertJson(['is_active' => false]);

        $material->refresh();
        $this->assertEquals(0, $material->is_active);
    }
}
