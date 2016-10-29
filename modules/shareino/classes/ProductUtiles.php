<?php
/**
 * 2015-2016 Shareino
 *
 * NOTICE OF LICENSE
 *
 * This source file is for module that make sync Product With shareino server
 * https://github.com/SaeedDarvish/PrestaShopShareinoModule
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Shareino to newer
 * versions in the future. If you wish to customize Shareino for your
 * needs please refer to https://github.com/SaeedDarvish/PrestaShopShareinoModule for more information.
 *
 * @author    Saeed Darvish <sd.saeed.darvish@gmail.com>
 * @copyright 2015-2016 Shareino Co
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  Tejarat Ejtemaie Eram
 */

require_once(dirname(__FILE__) . '/ShareinoSync.php');
require_once(dirname(__FILE__) . '/OrganizeCategories.php');

class ProductUtiles
{
    public $context;
    const SHAREINO_API_URL = "http://dev.scommerce.ir//api/v1/public/";

    public function __construct($context)
    {
        $this->context = $context;
    }

    /**
     * <p> Find product with {@link getProductDetailById} and send request to {@link http://shareino.com}
     *     with {@link sendRequset} by <code> POST </code> method
     * </p>
     * @param $productIds
     * @internal param $productId
     */
    public function syncProduct($productIds)
    {
        $products = array();
        $result = null;
        if (!is_array($productIds)) {
            $product = $this->getProductDetailById($productIds);
            if ($product) {
                $result = $this->sendRequset("products", "POST", Tools::jsonEncode($product));
            }
        } else {
            foreach ($productIds as $id) {
                $products[] = $this->getProductDetailById($id);
            }
            if (!empty($products)) {
                $result = $this->sendRequset("products", "POST", Tools::jsonEncode($products));
            }
        }
        return $this->parsSyncResult($result, $productIds);


    }

    public function bulkSync($ids, $product_ids = null)
    {
        set_time_limit(0);
        $sync = new ShareinoSync();

        $product_ids = $product_ids == null ? $sync->getProductsIds($ids) : $product_ids;

        if ($product_ids) {
            $split_ids = array_chunk($product_ids, 75);
            foreach ($split_ids as $pIds) {
                $this->syncProduct($pIds);
            }
        }
    }

    /**
     * Called when need to send request to external server or site
     *
     * @param $url url address af Server
     * @param null $body content of request like product
     * @param $method
     * @return mixed | null
     */
    public function sendRequset($url, $method, $body = null)
    {

        // Init curl
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // Generate url and set method in url
        $url = self::SHAREINO_API_URL . $url;
        curl_setopt($curl, CURLOPT_URL, $url);

        // Set method in curl
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        // Get token from site setting
        $SHAREINO_API_TOKEN = Configuration::get("SHAREINO_API_TOKEN");


        // Check if token has been set then send request to {@link http://shareino.com}
        if (!empty($SHAREINO_API_TOKEN)) {

            // Set Body if its exist
            if ($body != null) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization:Bearer $SHAREINO_API_TOKEN"));

            return curl_exec($curl);
        }
        return null;
    }


    public function parsSyncResult($results, $productIds = null)
    {
        $results = Tools::jsonDecode($results, true);

        if ($this->checkAuth($results)) {
            if (is_array($results)) {

                foreach ($results as $result) {
                    $shsync = new ShareinoSync($this->context);
                    $shsync->product_id = $result["code"];
                    $shsync->status = $result["status"];

                    $shsync->errors = isset($result["errors"]) & !empty($result["errors"]) ?
                        implode(", ", $result["errors"]) : "";
                    $shsync->syncLocalField();
                }
            }
            return;
        } else {

            if ($results["status"] == false) {
                foreach ($productIds as $ids) {
                    $shsync = new ShareinoSync($this->context);
                    $shsync->product_id = $ids;
                    $shsync->status = false;
                    $shsync->errors = implode(", ", $results["messages"]);
                    $shsync->syncLocalField();
                }
            }
            return;
        }
        $this->setAllFailure($productIds);
        //


    }

    public function setAllFailure($productIds)
    {
        foreach ($productIds as $ids) {
            $shsync = new ShareinoSync($this->context);
            $shsync->product_id = $ids;
            $shsync->status = false;
            $shsync->syncLocalField();
        }
    }

    public function getFrontFeaturesStatic($id_lang, $id_product)
    {
        $features = array();
        if (Feature::isFeatureActive()) {
            $query = 'SELECT name, value, pf.id_feature, liflv.url_name AS url
                FROM ' . _DB_PREFIX_ . 'feature_product pf
                LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON (fl.id_feature = pf.id_feature AND fl.id_lang = ' . (int)$id_lang . ')
                LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl ON (fvl.id_feature_value = pf.id_feature_value AND fvl.id_lang = ' . (int)$id_lang . ')
                LEFT JOIN ' . _DB_PREFIX_ . 'feature f ON (f.id_feature = pf.id_feature AND fl.id_lang = ' . (int)$id_lang . ')
                LEFT JOIN ' . _DB_PREFIX_ . 'layered_indexable_feature_lang_value liflv ON (f.id_feature = liflv.id_feature AND liflv.id_lang = ' . (int)$id_lang . ')
                ' . Shop::addSqlAssociation('feature', 'f') . '
                WHERE pf.id_product = ' . (int)$id_product . '
                ORDER BY f.position ASC';
            $features = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
            return $features;
        }
        return array();
    }

    public function getProductDetailById($productId = null)
    {

        $product = new Product($productId, false, $this->context->language->id);

        return $this->getProductDetail($product);
    }

    public function getProductDetail($product)
    {


        // Get All images and covers
        $images = Image::getImages($this->context->language->id, $product->id);

        $coverPath = "";
        $imagesPath = array();
        $link = new Link;//because getImageLInk is not static function
        foreach ($images as $image) {
            if ($image["cover"]) {
                $coverPath = $link->getImageLink($product->link_rewrite, $image['id_image'], 'large_default');
            } else {
                $imagesPath[] = $link->getImageLink($product->link_rewrite, $image['id_image'], 'large_default');
            }
        }

        // Get Variant
        $vars = $product->getAttributeCombinations($this->context->language->id);
        $variations = array();
        $price = $product->getPrice();
        foreach ($vars as $var) {
            $groupName = Tools::strtolower($var["group_name"]);
            $groupName = str_replace(" ", "_", $groupName);

            $variations[$var["id_product_attribute"]]["variation"][$groupName] = array(
                "label" => $var["group_name"],
                "value" => $var["attribute_name"]
            );

            $variations[$var["id_product_attribute"]]["code"] = $var["id_product_attribute"];
            $variations[$var["id_product_attribute"]]["quantity"] = $var["quantity"];
            $variations[$var["id_product_attribute"]]["price"] = $var["price"] <= 0 ? $price : $var["price"];

        }

        // Get All Product Attributes
        $features = $this->getFrontFeaturesStatic($this->context->language->id, $product->id);

        $attributes = array();
        foreach ($features as $feature) {
            $slug = $feature["url"];
            $slug = $slug == null && $slug == "" ?
                Tools::strtolower($feature["name"]) : $slug;
            $attributes[$slug] = array(
                "label" => $feature['name'],
                "value" => $feature["value"]
            );
        }

        // Get All Categories
        $categories = Product::getProductCategoriesFull($product->id, $this->context->language->id);

        $productCategories = OrganizeCategories::getShareinoids($categories);

        // Get All Convert Factor
        $pricefactor = Configuration::get("SHAREINO_PRICE_FACTOR");
        $weightFactor = Configuration::get("SHAREINO_WEIGHT_FACTOR");

        $pricefactor = is_numeric($pricefactor) ? $pricefactor : 1;
        $weightFactor = is_numeric($pricefactor) ? $weightFactor : 1;


        $tags = $product->getTags($this->context->language->id);
        $product_detail = array(
            "name" => $product->name,
            "code" => $product->id,
            "sku" => $product->reference,
//            "price" => $price * $pricefactor,
            "price" => $price,
            "sale_price" => "",
            "discount" => "",
            "quantity" => Product::getQuantity($product->id),
//            "weight" => $product->weight * $weightFactor,
            "weight" => $product->weight,
            "original_url" => $link->getProductLink($product),
            "brand_id" => "",
            "categories" => $productCategories,
            "short_content" => $product->description_short,
            "long_content" => $product->description,
            "meta_keywords" => $product->meta_keywords,
            "meta_description" => $product->meta_description,
            "image" => $coverPath,
            "images" => $imagesPath,
            "attributes" => $attributes,
            "variants" => $variations,
            "tags" => $tags
        );

        return $product_detail;
    }

    public function deleteProducts($ids, $all = false)
    {
        $body = array();
        $url = "products";

        // Chek if want to delete All product
        if ($all) {
            $body = array("type" => "all");
        } else {
            // check if want to delete multiple
            if (is_array($ids)) {
                $body = array("type" => "selected", "code" => $ids);
            }
            else {
                // if want to delete once
                $url .= "/$ids";
            }
        }

        $result = $this->sendRequset($url, "DELETE", Tools::jsonEncode($body));
        return Tools::jsonDecode($result, true);

    }

    public function checkAuth($input)
    {
        if (isset($input["status"]) & !$input["status"]) {
            if (substr_compare($input["message"][0], "Invalid authorization token.") == 0) {
                $this->context->cookie->__set('redirect_errors', Tools::displayError('Invalid Shareino authorization token.'));
                return false;
            }
        }
        return true;
    }
}
