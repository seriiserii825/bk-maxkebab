<?php
function get_products_by_query($query_args)
{
  $query = new WP_Query($query_args);
  $products = [];

  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();
      $product_id = get_the_ID();
      $product    = wc_get_product($product_id);

      $regular_price = $product->get_regular_price();
      $sale_price    = $product->get_sale_price();
      $is_on_sale    = $product->is_on_sale() && $sale_price;

      $slug = get_post_field('post_name', $product_id);
      $permalink = 'produs' . '/' . $slug;

      $products[] = [
        'id'            => $product_id,
        'title'         => get_the_title(),
        'slug'          => $slug,
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
