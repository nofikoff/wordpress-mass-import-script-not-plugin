<?php
//
// ИСТОЧНИК
// https://stackoverflow.com/questions/52937409/create-programmatically-a-product-using-crud-methods-in-woocommerce-3


// Custom function for product creation (For Woocommerce 3+ only)
function create_product($args)
{
    global $woocommerce;

    if (!function_exists('wc_get_product_object_type') && !function_exists('wc_prepare_product_attributes'))
        return false;

    // Get an empty instance of the product object (defining it's type)
    $product = wc_get_product_object_type($args['type']);
    if (!$product)
        return false;

    // Product name (Title) and slug
    $product->set_name($args['name']); // Name (title).
    if (isset($args['slug']))
        $product->set_name($args['slug']);

    // Description and short description:
    $product->set_description($args['description']);
    $product->set_short_description($args['short_description']);

    // Status ('publish', 'pending', 'draft' or 'trash')
    $product->set_status(isset($args['status']) ? $args['status'] : 'publish');

    // Visibility ('hidden', 'visible', 'search' or 'catalog')
    $product->set_catalog_visibility(isset($args['visibility']) ? $args['visibility'] : 'visible');

    // Featured (boolean)
    $product->set_featured(isset($args['featured']) ? $args['featured'] : false);

    // Virtual (boolean)
    $product->set_virtual(isset($args['virtual']) ? $args['virtual'] : false);

    // Prices
    $product->set_regular_price($args['regular_price']);
    $product->set_sale_price(isset($args['sale_price']) ? $args['sale_price'] : '');
    $product->set_price(isset($args['sale_price']) ? $args['sale_price'] : $args['regular_price']);
    if (isset($args['sale_price'])) {
        $product->set_date_on_sale_from(isset($args['sale_from']) ? $args['sale_from'] : '');
        $product->set_date_on_sale_to(isset($args['sale_to']) ? $args['sale_to'] : '');
    }

    // Downloadable (boolean)
    $product->set_downloadable(isset($args['downloadable']) ? $args['downloadable'] : false);
    if (isset($args['downloadable']) && $args['downloadable']) {
        $product->set_downloads(isset($args['downloads']) ? $args['downloads'] : array());
        $product->set_download_limit(isset($args['download_limit']) ? $args['download_limit'] : '-1');
        $product->set_download_expiry(isset($args['download_expiry']) ? $args['download_expiry'] : '-1');
    }

    // Taxes
    if (get_option('woocommerce_calc_taxes') === 'yes') {
        $product->set_tax_status(isset($args['tax_status']) ? $args['tax_status'] : 'taxable');
        $product->set_tax_class(isset($args['tax_class']) ? $args['tax_class'] : '');
    }

    // SKU and Stock (Not a virtual product)
    if (isset($args['virtual']) && !$args['virtual']) {
        $product->set_sku(isset($args['sku']) ? $args['sku'] : '');
        $product->set_manage_stock(isset($args['manage_stock']) ? $args['manage_stock'] : false);
        $product->set_stock_status(isset($args['stock_status']) ? $args['stock_status'] : 'instock');
        if (isset($args['manage_stock']) && $args['manage_stock']) {
            $product->set_stock_status($args['stock_qty']);
            $product->set_backorders(isset($args['backorders']) ? $args['backorders'] : 'no'); // 'yes', 'no' or 'notify'
        }
    }

    // Sold Individually
    $product->set_sold_individually(isset($args['sold_individually']) ? $args['sold_individually'] : false);

    // Weight, dimensions and shipping class
    $product->set_weight(isset($args['weight']) ? $args['weight'] : '');
    $product->set_length(isset($args['length']) ? $args['length'] : '');
    $product->set_width(isset($args['width']) ? $args['width'] : '');
    $product->set_height(isset($args['height']) ? $args['height'] : '');
    if (isset($args['shipping_class_id']))
        $product->set_shipping_class_id($args['shipping_class_id']);

    // Upsell and Cross sell (IDs)
    $product->set_upsell_ids(isset($args['upsells']) ? $args['upsells'] : '');
    $product->set_cross_sell_ids(isset($args['cross_sells']) ? $args['upsells'] : '');

    // Attributes et default attributes
    if (isset($args['attributes']))
        $product->set_attributes(wc_prepare_product_attributes($args['attributes']));
    if (isset($args['default_attributes']))
        $product->set_default_attributes($args['default_attributes']); // Needs a special formatting

    // Reviews, purchase note and menu order
    $product->set_reviews_allowed(isset($args['reviews']) ? $args['reviews'] : false);
    $product->set_purchase_note(isset($args['note']) ? $args['note'] : '');
    if (isset($args['menu_order']))
        $product->set_menu_order($args['menu_order']);

    // Product categories and Tags
    if (isset($args['category_ids']))
        $product->set_category_ids('category_ids');

    if (isset($args['tag_ids']))
        $product->set_tag_ids($args['tag_ids']);


    // Images and Gallery
    $product->set_image_id(isset($args['image_id']) ? $args['image_id'] : "");
    $product->set_gallery_image_ids(isset($args['gallery_ids']) ? $args['gallery_ids'] : array());

    ## --- SAVE PRODUCT --- ##
    $product_id = $product->save();

    return $product_id;
}

// Utility function that returns the correct product object instance
function wc_get_product_object_type($type)
{
    // Get an instance of the WC_Product object (depending on his type)
    if (isset($args['type']) && $args['type'] === 'variable') {
        $product = new WC_Product_Variable();
    } elseif (isset($args['type']) && $args['type'] === 'grouped') {
        $product = new WC_Product_Grouped();
    } elseif (isset($args['type']) && $args['type'] === 'external') {
        $product = new WC_Product_External();
    } else {
        $product = new WC_Product_Simple(); // "simple" By default
    }

    if (!is_a($product, 'WC_Product'))
        return false;
    else
        return $product;
}

// Utility function that prepare product attributes before saving
function wc_prepare_product_attributes($attributes)
{
    global $woocommerce;

    $data = array();
    $position = 0;

    foreach ($attributes as $taxonomy => $values) {
        if (!taxonomy_exists($taxonomy))
            continue;

        // Get an instance of the WC_Product_Attribute Object
        $attribute = new WC_Product_Attribute();

        $term_ids = array();

        // Loop through the term names
        foreach ($values['term_names'] as $term_name) {
            if (term_exists($term_name, $taxonomy))
                // Get and set the term ID in the array from the term name
                $term_ids[] = get_term_by('name', $term_name, $taxonomy)->term_id;
            else
                continue;
        }

        $taxonomy_id = wc_attribute_taxonomy_id_by_name($taxonomy); // Get taxonomy ID

        $attribute->set_id($taxonomy_id);
        $attribute->set_name($taxonomy);
        $attribute->set_options($term_ids);
        $attribute->set_position($position);
        $attribute->set_visible($values['is_visible']);
        $attribute->set_variation($values['for_variation']);

        $data[$taxonomy] = $attribute; // Set in an array

        $position++; // Increase position
    }
    return $data;
}


function upload_image($image_url)
{
    $upload_dir = wp_upload_dir();

    $image_data = file_get_contents($image_url);

    $filename = basename($image_url);

    if (wp_mkdir_p($upload_dir['path'])) {
        $file = $upload_dir['path'] . '/' . $filename;
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }

    file_put_contents($file, $image_data);

    $wp_filetype = wp_check_filetype($filename, null);

    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );

    $attach_id = wp_insert_attachment($attachment, $file);
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $file);
    wp_update_attachment_metadata($attach_id, $attach_data);
    return $attach_id;
}


// верме категории второго и третьего уровня в виде ID => NAME
function my_get_categories($product_name)
{
    if (preg_match('~-(\d+)~', $product_name, $m) and
        preg_match('~^([^-\s]+)~', $product_name, $n)) {
        $type_name_from_product = $n[1];
        $type_kilovat_from_product = $m[1];
    }


    $taxonomy = 'product_cat';
    $orderby = 'name';
    $show_count = 0;      // 1 for yes, 0 for no
    $pad_counts = 0;      // 1 for yes, 0 for no
    $hierarchical = 1;      // 1 for yes, 0 for no
    $title = '';
    $empty = 0; // пустеы тоже == 0

    $args = array(
        'taxonomy' => $taxonomy,
        'orderby' => $orderby,
        'show_count' => $show_count,
        'pad_counts' => $pad_counts,
        'hierarchical' => $hierarchical,
        'title_li' => $title,
        'hide_empty' => $empty
    );
    $all_categories = get_categories($args);
    $result = [];
    foreach ($all_categories as $cat) {
        // только те в названии которых есть слово киловольт
        if (strpos($cat->name, 'кило')) {
            if (preg_match('~на (\d+) кило~u', $cat->name, $m)
                and
                preg_match('~^([^-\s]+)~', $cat->name, $n)) {

                //echo "*** {$n[1]} на {$m[1]} ***\n";

                // сраниваемимя твара и категории
                if ($type_name_from_product == $n[1]
                    and $type_kilovat_from_product == $m[1]
                ) {
                    // категория определена
                    return $cat->term_id;
                }


            }
            //echo $cat->name . "\n";

            //-(\d+) мощность
            //^([^-\s]+)
        }

//            $args2 = array(
//                'taxonomy' => $taxonomy,
//                'child_of' => 0,
//                'parent' => $category_id,
//                'orderby' => $orderby,
//                'show_count' => $show_count,
//                'pad_counts' => $pad_counts,
//                'hierarchical' => $hierarchical,
//                'title_li' => $title,
//                'hide_empty' => $empty
//            );
//            $sub_cats = get_categories($args2);
//            if ($sub_cats) {
//                foreach ($sub_cats as $sub_category) {
//                    echo $sub_category->name . " parent -> " . $cat->category_parent;
//                }
//            }
        //}
    }

    echo " ".$type_name_from_product . " на " . $type_kilovat_from_product . " киловольт НЕ НАЙДЕНА для $product_name\n";
    return 0;
}


function getListProductsInCategory($url_category)
{
    $content = file_get_contents($url_category);
    $content = mb_convert_encoding($content, "UTF-8", "WINDOWS-1251");
    $result = [];

    echo $url_category ." смотрим список товаров \n";

    if (preg_match_all('~TITLE="Показать выбранное изделие" HREF="([^"]+?)">~um', $content, $urls)
        and preg_match_all('~<SPAN CLASS="UK_Tbb">([^<]+?)<\/SPAN> ([^<]+?)&nbsp <\/A>~um', $content, $named)
    ) {

        foreach ($urls[1] as $key => $url) {
            $result[$key] = [
                'name' => $named[1][$key] . " " . $named[2][$key],
                'shortname' => $named[1][$key],
                'url' => "http://www.yuzhcable.info/" . $url
            ];
        }

    }
    return $result;

}

function getCartProduct($url_product)
{
    $content = file_get_contents($url_product);
    $content = mb_convert_encoding($content, "UTF-8", "WINDOWS-1251");
    $result = [];

    if (preg_match_all("~UK_TextDescr = '(.*?)'~um", $content, $desc)
        and preg_match_all("~UK_TextChar = '(.*?)'~um", $content, $char)
        and preg_match_all('~UK_Cable" src="([^"]+?)">~um', $content, $images)
    ) {
        //
        $result = [
            'desc' => $desc[1][0],
            'char' => $char[1][0],
            // только последнюю картинку берем
            'img' => "http://www.yuzhcable.info/" . end($images[1])
        ];
    }
    return $result;

}
