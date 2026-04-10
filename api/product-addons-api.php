<?php
if (!defined('ABSPATH')) exit;

add_action('rest_api_init', function () {
  register_rest_route('custom/v1', '/product-addons', [
    'methods'             => 'GET',
    'permission_callback' => '__return_true',
    'callback'            => function (WP_REST_Request $req) {
      $product_id = (int) $req->get_param('product_id');

      if (!$product_id) {
        return new WP_Error('missing_param', 'product_id is required', ['status' => 400]);
      }

      if (!function_exists('pewc_get_extra_fields')) {
        return [];
      }

      $extra_fields = pewc_get_extra_fields($product_id);

      if (empty($extra_fields)) {
        return [];
      }

      $groups = [];

      foreach ($extra_fields as $group_id => $group) {
        $items = $group['items'] ?? [];
        if (empty($items)) continue;

        // Global groups store title in 'meta'; product-specific groups use the post title.
        if (!empty($group['meta']['group_title'])) {
          $title = $group['meta']['group_title'];
          $description = $group['meta']['group_description'] ?? '';
        } elseif (function_exists('pewc_get_group_title')) {
          $title = pewc_get_group_title($group_id, $group);
          $description = function_exists('pewc_get_group_description')
            ? pewc_get_group_description($group_id, $group)
            : '';
        } else {
          $title = get_the_title($group_id) ?: '';
          $description = '';
        }

        $fields = [];
        foreach ($items as $field_id => $field) {
          $options = [];
          if (!empty($field['field_options']) && is_array($field['field_options'])) {
            foreach ($field['field_options'] as $opt) {
              $image_url = '';
              if (!empty($opt['image'])) {
                $image_url = wp_get_attachment_image_url($opt['image'], 'thumbnail') ?: '';
              }
              $options[] = [
                'label' => $opt['label'] ?? $opt['name'] ?? $opt['value'] ?? '',
                'price' => (float) ($opt['price'] ?? 0),
                'image' => $image_url,
              ];
            }
          }

          $required = !empty($field['field_required']) && $field['field_required'] !== 'no';

          $fields[] = [
            'id'          => (int) ($field['field_id'] ?? $field_id),
            'label'       => $field['field_label'] ?? '',
            'type'        => $field['field_type'] ?? 'text',
            'required'    => $required,
            'description' => $field['field_description'] ?? '',
            'price'       => (float) ($field['field_price'] ?? 0),
            'percentage'  => (float) ($field['field_percentage'] ?? 0),
            'min'         => (string) ($field['field_minchars'] ?? $field['field_minval'] ?? ''),
            'max'         => (string) ($field['field_maxchars'] ?? $field['field_maxval'] ?? ''),
            'options'     => $options,
          ];
        }

        if (empty($fields)) continue;

        $groups[] = [
          'id'          => (int) $group_id,
          'title'       => $title,
          'description' => $description,
          'fields'      => $fields,
        ];
      }

      return $groups;
    },
  ]);
});
