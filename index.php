<?php
// импортируем новые товары
include('../wp-load.php');
require 'class_add_product.php';


$list = [

//    "http://www.yuzhcable.info/index.php?CAT=11&MRI=110101",
//    "http://www.yuzhcable.info/index.php?CAT=11&MRI=110103",
//    "http://www.yuzhcable.info/index.php?CAT=11&MRI=110104",
//    "http://www.yuzhcable.info/index.php?CAT=11&MRI=110105",
//    "http://www.yuzhcable.info/index.php?CAT=11&MRI=110106",
//500

     "http://www.yuzhcable.info/index.php?CAT=11&MRI=110201",
     "http://www.yuzhcable.info/index.php?CAT=10&MRI=100101",
     "http://www.yuzhcable.info/index.php?CAT=10&MRI=100102",
     "http://www.yuzhcable.info/index.php?CAT=10&MRI=100103",
     "http://www.yuzhcable.info/index.php?CAT=10&MRI=100104",
     "http://www.yuzhcable.info/index.php?CAT=10&MRI=100105",
//1350

     "http://www.yuzhcable.info/index.php?CAT=10&MRI=100106",
     "http://www.yuzhcable.info/index.php?CAT=10&MRI=100107",
     "http://www.yuzhcable.info/index.php?CAT=10&MRI=100409",
     "http://www.yuzhcable.info/index.php?CAT=10&MRI=100410",
     "http://www.yuzhcable.info/index.php?CAT=10&MRI=100411",
     "http://www.yuzhcable.info/index.php?CAT=10&MRI=100412",
//1850

//     "http://www.yuzhcable.info/index.php?CAT=15&MRI=150701",
//     "http://www.yuzhcable.info/index.php?CAT=15&MRI=150702",
//     "http://www.yuzhcable.info/index.php?CAT=15&MRI=150801",
//     "http://www.yuzhcable.info/index.php?CAT=15&MRI=150802",
//     "http://www.yuzhcable.info/index.php?CAT=15&MRI=152201",
//     "http://www.yuzhcable.info/index.php?CAT=15&MRI=152301",
//     "http://www.yuzhcable.info/index.php?CAT=15&MRI=152302",
//     "http://www.yuzhcable.info/index.php?CAT=15&MRI=152401",
//     "http://www.yuzhcable.info/index.php?CAT=18&MRI=180101",
//     "http://www.yuzhcable.info/index.php?CAT=18&MRI=180108",
//     "http://www.yuzhcable.info/index.php?CAT=18&MRI=180109",
//     "http://www.yuzhcable.info/index.php?CAT=18&MRI=180116",
//     "http://www.yuzhcable.info/index.php?CAT=18&MRI=180117",
//     "http://www.yuzhcable.info/index.php?CAT=18&MRI=180123",
//     "http://www.yuzhcable.info/index.php?CAT=20&MRI=200105",
//     "http://www.yuzhcable.info/index.php?CAT=20&MRI=200106",
//     "http://www.yuzhcable.info/index.php?CAT=20&MRI=200107",
//     "http://www.yuzhcable.info/index.php?CAT=20&MRI=200205",
//     "http://www.yuzhcable.info/index.php?CAT=20&MRI=200206",
//     "http://www.yuzhcable.info/index.php?CAT=20&MRI=200303",
];
$product_list = [];


// списки продуктов два уровня
foreach ($list as $item) {
    //echo $item;
    $product_list[] = getListProductsInCategory($item);
}

// перебираемтовары два уровня
foreach ($product_list as $item) {
    foreach ($item as $product) {

        //print_r($product['shortname']);

        //    [name] => АПвЭВнг-150 1x300 ТУ У 31.3-00214534-060:2011
        //    [shortname] => АПвЭВнг-150 1x300
        //    [url] => http://www.yuzhcable.info/edata.php?MRR=110101150000000300
        // картинка описание в карточке товара
        $product_cart = getCartProduct($product['url']);
        //
        $category_id = my_get_categories($product['shortname']);


        // вытащить картинки - JSON
        // подправить категории чтобы одинаковые были со словом НА
        // возможно удалить все товары ?? на тестовой базе показать СТАСУ

        // по умолчанию БЕЗ КАТЕГОРИИ
        if ($category_id == 0) $category_id = 283;

        $img_id = upload_image($product_cart['img']);

        $product_id = create_product(
            [
                'type' => '', // Simple product by default
                'name' => $product['name'],
                'description' => str_replace('&nbsp', ' ', $product_cart['desc'] . $product_cart['char']),
                'short_description' => '',
                'regular_price' => '1.00', // product priceцена обязательно ибо заказать нельзя будет товар без цены
                'image_id' => $img_id,
            ]
        );

        // привязать категорию
        wp_set_object_terms($product_id, $category_id, 'product_cat');

        echo " #".$product_id . " ";


    }

}
