<?php
if (!defined('ABSPATH')) exit;

require_once get_template_directory() . '/api/helpers/get_wp_products.php';

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

function home_get_catalog()
{
  $home_page_id      = 44;
  $products_sections = get_field('products_sections', $home_page_id);
  $products_section  = $products_sections['products_section'] ?? [];

  $catalog = [];

  foreach ($products_section as $item) {
    $title      = $item['title'];
    $scroll_id = $item['scroll_id'];
    $category   = $item['category'];
    $icons      = $item['icons'];
    $images     = $item['images'];
    $background = $item['background'];

    $term = is_a($category, 'WP_Term') ? $category : get_term_by('slug', $category, 'product_cat');
    if (!$term) continue;

    $child_categories = get_terms('product_cat', [
      'parent'     => $term->term_id,
      'hide_empty' => false,
    ]);

    $has_children = !empty($child_categories);
    $children     = [];

    if ($has_children) {
      foreach ($child_categories as $child) {
        $children[] = get_wp_products($child);
      }
    } else {
      $children[] = get_wp_products($term);
    }

    $catalog[] = [
      'id'           => $term->term_id,
      'name'         => $title,
      'scroll_id'    => $scroll_id,
      'slug'         => $term->slug,
      'has_children' => $has_children,
      'children'     => $children,
      'icons'        => $icons,
      'images'       => $images,
      'background'   => $background,
    ];
  }

  return $catalog;
}
