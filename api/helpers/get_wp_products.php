<?php function get_wp_products($term)
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
