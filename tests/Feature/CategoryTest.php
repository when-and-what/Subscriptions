<?php

use App\Models\Category;
use App\Models\Service;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $category = Category::factory()->create();

    $this->get(route('categories.index'))->assertRedirect(route('login'));
    $this->get(route('categories.create'))->assertRedirect(route('login'));
    $this->get(route('categories.edit', $category))->assertRedirect(route('login'));
});

test('a user can list their own categories', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create();
    Category::factory()->create(); // another user's category

    $response = $this->actingAs($user)->get(route('categories.index'));

    $response->assertOk();
    $response->assertSee($category->name);
});

test('a user can view the create and edit forms', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create();

    $this->actingAs($user)->get(route('categories.create'))->assertOk();
    $this->actingAs($user)->get(route('categories.edit', $category))->assertOk();
});

test('a user can create a category', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('categories.store'), [
        'name' => 'Streaming',
    ]);

    $response->assertRedirect(route('categories.index'));
    expect(Category::where('user_id', $user->id)->where('name', 'Streaming')->exists())->toBeTrue();
});

test('a user can update their own category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create();

    $response = $this->actingAs($user)->put(route('categories.update', $category), [
        'name' => 'Updated Name',
    ]);

    $response->assertRedirect(route('categories.index'));
    expect($category->refresh()->name)->toBe('Updated Name');
});

test('a user can delete their own category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create();

    $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

    $response->assertRedirect(route('categories.index'));
    expect(Category::find($category->id))->toBeNull();
});

test('a user cannot edit another user\'s category', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $category = Category::factory()->for($owner)->create();

    $this->actingAs($intruder)->get(route('categories.edit', $category))->assertForbidden();
    $this->actingAs($intruder)->put(route('categories.update', $category), ['name' => 'Hacked'])->assertForbidden();
    $this->actingAs($intruder)->delete(route('categories.destroy', $category))->assertForbidden();
});

test('deleting a category removes it from associated services', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create();
    $service = Service::factory()->for($user)->create();
    $service->categories()->attach($category);

    $this->actingAs($user)->delete(route('categories.destroy', $category));

    expect($service->categories()->count())->toBe(0);
});
