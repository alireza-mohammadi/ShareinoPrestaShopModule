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
    const SHAREINO_API_URL = "http://dev.scommerce.ir/api/v1/public/";

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
            if ($product && $product != null) {
                $result = $this->sendRequset("products", "POST", Tools::jsonEncode($product));
            }
        } else {
            foreach ($productIds as $id) {
                $product = $this->getProductDetailById($id);
                if ($product && $product != null && $product["active"]) {
                    $products[] = $product;
                }
            }
            if (!empty($products)) {
                $result = $this->sendRequset("products", "POST", Tools::jsonEncode($products));
            }
        }
        if ($result !== null)
            return $this->parsSyncResult($result, $productIds);
        else
            return null;


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
     * @param $method
     * @param null $body content of request like product
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
//            curl_setopt($curl, CURLOPT_HEADER, true);    // we want headers

            $shareinoModule = Module::getInstanceByName('shareino');

            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    "Authorization:Bearer $SHAREINO_API_TOKEN",
                    "User-Agent:PrestaShop_Module_$shareinoModule->version"
                )
            );

            // Get result
            $result = curl_exec($curl);

            // Get Header Response header
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($httpcode === 401 || $httpcode === 403) {

                Tools::displayError("خطا ! لطفا صحت توکن و وضعیت دسترسی به وب سرویس شیرینو را بررسی کنید");
                $this->context->smarty->assign('error', 'fuck!');
                return null;

            }
            return $result;
        } else {
            Tools::displayError("توکن وارد نشده است");
            return null;
        }
        return null;
    }


    public function parsSyncResult($results, $productIds = null)
    {
        $results = Tools::jsonDecode($results, true);

        if ($results != null) {
            if (is_array($results)) {

                foreach ($results as $result) {

                    if (!isset($result["code"]) | $result["code"] == null) {
                        continue;
                    }
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

            foreach ($productIds as $ids) {
                if (!isset($result["code"]) | $result["code"] == null) {
                    continue;
                }
                $shsync = new ShareinoSync($this->context);
                $shsync->product_id = $ids;
                $shsync->status = false;
                $shsync->errors = implode(", ", $results["messages"]);
                $shsync->syncLocalField();
            }
            return;
        }

        $this->setAllFailure($productIds);
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
        if ($product->id == null) {
            $shareinoSync = new ShareinoSync();
            $shareinoSync->deleteProduct($productId);
            return null;
        }
        return $this->getProductDetail($product);
    }

    public function getProductDetail($product)
    {
        // Check Availability of sell out of stock products
        $stockAvalible = new StockAvailableCore($product->id, $this->context->language->id);
        $out_of_stock = $stockAvalible->out_of_stock;
        if ($out_of_stock == 2)
            $out_of_stock = ConfigurationCore::get("PS_ORDER_OUT_OF_STOCK");

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
        $priceWithoutReduct = $product->getPriceWithoutReduct(Product::$_taxCalculationMethod == PS_TAX_INC);


        foreach ($vars as $var) {
            $groupName = Tools::strtolower($var["group_name"]);
            $groupName = str_replace(" ", "_", $groupName);

            $variations[$var["id_product_attribute"]]["variation"][$groupName] = array(
                "label" => $var["group_name"],
                "value" => $var["attribute_name"]
            );

            $variations[$var["id_product_attribute"]]["code"] = $var["id_product_attribute"];
            $variations[$var["id_product_attribute"]]["default_value"] = $var["default_on"];
            $variations[$var["id_product_attribute"]]["quantity"] = $var["quantity"];
            $variations[$var["id_product_attribute"]]["price"] = $product->getPriceWithoutReduct(Product::$_taxCalculationMethod == PS_TAX_INC, $var["id_product_attribute"]);


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

        $tags = $product->getTags($this->context->language->id);
        $tags = explode(",", $tags);

        $product_detail = array(
            "name" => $product->name,
            "code" => $product->id,
            "sku" => $product->reference,
            "price" => $priceWithoutReduct,
            "active" => $product->active,
            "sale_price" => "",
            "discount" => "",
            "quantity" => Product::getQuantity($product->id),
            "weight" => $product->weight,
            "available_for_order" => $product->available_for_order,
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
            "out_of_stock" => $out_of_stock,
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
            if (is_array($ids)) {
                $body = array("type" => "selected", "code" => $ids);
            } else {
                $url .= "/$ids";
            }
        }

        $result = $this->sendRequset($url, "DELETE", Tools::jsonEncode($body));

        return Tools::jsonDecode($result, true);

    }

}
