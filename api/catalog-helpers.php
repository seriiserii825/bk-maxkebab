<?php
if (!defined('ABSPATH')) exit;

function home_product_has_options($product_id)
{
  if (function_exists('pewc_get_extra_fields')) {
    $extra_fields = pewc_get_extra_fields($product_id);
    if (!empty($extra_fields)) return true;
  }

  $product_extra_options = get_post_meta($product_id, 'pewc_product_extra_options', true);
  if (!empty($product_extra_options)) return true;

  $global_groups = get_posts([
    'post_type'      => 'pewc_group',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'fields'         => 'ids',
  ]);

  $product_categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'ids']);

  foreach ($global_groups as $group_id) {
    $assigned_products = get_post_meta($group_id, 'pewc_group_products', true);
    if (is_array($assigned_products) && in_array($product_id, $assigned_products)) return true;

    $group_categories = get_post_meta($group_id, 'pewc_group_categories', true);
    if (is_array($group_categories) && array_intersect($product_categories, $group_categories)) return true;
  }

  return false;
}

function home_get_products_by_category($category_slug)
{
  $query = new WP_Query([
    'post_type'      => 'product',
    'posts_per_page' => -1,
    'tax_query'      => [[
      'taxonomy' => 'product_cat',
      'field'    => 'slug',
      'terms'    => $category_slug,
    ]],
  ]);

  $products = [];

  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();
      $product_id = get_the_ID();
      $product    = wc_get_product($product_id);

      $regular_price = $product->get_regular_price();
      $sale_price    = $product->get_sale_price();
      $is_on_sale    = $product->is_on_sale() && $sale_price;

      $slug = $product->get_slug();
      $permalink = 'produs' . '/' . $slug;

      $products[] = [
        'id'            => $product_id,
        'title'         => get_the_title(),
        'slug'          => $product->get_slug(),
        'permalink'     => $permalink,
        'image'         => get_the_post_thumbnail_url($product_id, 'post-thumbnail'),
        'description'   => parse_globus_content(get_the_content()),
        'sku'           => $product->get_sku(),
        'regular_price' => $regular_price,
        'sale_price'    => $is_on_sale ? $sale_price : null,
        'is_on_sale'    => $is_on_sale,
        'has_options'   => home_product_has_options($product_id),
      ];
    }
    wp_reset_postdata();
  }

  return $products;
}

function home_get_category_data($term)
{
  $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
  $image_url    = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : null;

  return [
    'id'       => $term->term_id,
    'name'     => $term->name,
    'slug'     => $term->slug,
    'image'    => $image_url,
    'products' => home_get_products_by_category($term->slug),
  ];
}

function home_get_catalog()
{
  // $products_sections = get_field('products_sections');
  // $products_section = $products_sections['products_section'];
  // $title = $item['title'];
  // $category = $item['category'];
  // $icons = $item['icons'];
  // $images = $item['images'];
  // $background = $item['background'];

  $parent_categories = get_terms('product_cat', [
    'order'      => 'ASC',
    'hide_empty' => true,
    'parent'     => 0,
  ]);

  $catalog = [];

  foreach ($parent_categories as $parent) {
    $child_categories = get_terms('product_cat', [
      'parent'     => $parent->term_id,
      'hide_empty' => false,
    ]);

    $has_children = !empty($child_categories);
    $children     = [];

    if ($has_children) {
      foreach ($child_categories as $child) {
        $children[] = home_get_category_data($child);
      }
    } else {
      $children[] = home_get_category_data($parent);
    }

    $catalog[] = [
      'id'           => $parent->term_id,
      'name'         => $parent->name,
      'slug'         => $parent->slug,
      'has_children' => $has_children,
      'children'     => $children,
    ];
  }

  return $catalog;
}
