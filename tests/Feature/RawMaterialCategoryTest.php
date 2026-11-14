<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RawMaterialCategoryTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::factory()->create([
            'email' => 'admin@admin.com',
        ]);
    }

    public function test_can_view_raw_material_categories_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('raw_material_categories.index'));
        $response->assertStatus(200);
        $response->assertSee('Raw Material Category');
    }

    public function test_can_create_raw_material_category(): void
    {
        $response = $this->actingAs($this->user)->post(route('raw_material_categories.store'), [
            'name' => 'Premium Silk Fabrics',
        ]);

        $response->assertRedirect(route('raw_material_categories.index'));

        $category = Category::where('name', 'Premium Silk Fabrics')->first();
        $this->assertNotNull($category);
        $this->assertEquals(1, $category->is_raw_material);
    }

    public function test_cannot_create_duplicate_raw_material_category(): void
    {
        Category::create([
            'name' => 'Fancy Buttons',
            'is_raw_material' => 1,
        ]);

        $response = $this->actingAs($this->user)->post(route('raw_material_categories.store'), [
            'name' => 'Fancy Buttons',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_can_update_raw_material_category(): void
    {
        $category = Category::create([
            'name' => 'Old Category Name',
            'is_raw_material' => 1,
        ]);

        $response = $this->actingAs($this->user)->put(route('raw_material_categories.update', $category->id), [
            'name' => 'Updated Category Name',
        ]);

        $response->assertRedirect(route('raw_material_categories.index'));

        $category->refresh();
        $this->assertEquals('Updated Category Name', $category->name);
    }

    public function test_cannot_delete_category_with_assigned_materials(): void
    {
        $category = Category::create([
            'name' => 'Fabric With Material',
            'is_raw_material' => 1,
        ]);

        Product::create([
            'creater_id' => $this->user->id,
            'category_id' => $category->id,
            'item_name' => 'Test Cotton Product',
            'item_code' => 'RM-TEST-CAT-01',
            'purchase_price_per_piece' => 100,
            'is_raw_material' => 1,
            'is_part' => 1,
            'is_active' => 1,
            'total_m2' => 0,
            'size_mode' => 'by_pieces',
        ]);

        $response = $this->actingAs($this->user)->delete(route('raw_material_categories.destroy', $category->id));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
