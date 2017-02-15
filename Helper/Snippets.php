<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @author     Bastien Lamamy  <bastien.lamamy@gmail.com> : http://bastien-lamamy.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mynamespace_Mymodule_Helper_Snippets extends Mage_Core_Helper_Data {
    
    const BUNDLE = 'bundle';

    protected $_product;
    protected $_companyData;
    protected $_richSnippets;
    protected $_homepageData;
    protected $_childProductsCount = 0;

    /**
     * Return Rich Snippets for product
     * Format Json
     *
     * @param $product
     * @return string
     */
    public function getProductRichSnippets($product)
    {
        $this->_product = $product;


        if($this->_product->isConfigurable()){
            $this->_configurableProduct();

        } elseif($this->_product->getTypeId() == self::BUNDLE){
            $this->_bundleProduct();

        } else {
            $this->_simpleProduct();
        }



    return Mage::helper('core')->jsonEncode($this->_richSnippets);
    }

    /**
     * Save in richSnippets snippets data for simple product
     *
     */
    protected function _simpleProduct() 
    {
        $this->_richSnippets = $this->_productGlobalInfo();
        $this->_richSnippets['offers'] = array(
                "@type"          => "Offer",
                "availability"   => $this->_isInStock(),
                "priceCurrency"  => Mage::app()->getStore()->getCurrentCurrencyCode(),
                "price"          => Mage::helper('core')->currency($this->_product->getFinalPrice(), false, false)
        );

    }

    /**
     * Save in richSnippets snippets data for configurable product
     *
     */
    protected function _configurableProduct() 
    {
        $price_array                   = $this->_getLowestHighestPrice(); // array containing required values
        $this->_richSnippets           = $this->_productGlobalInfo();
        $this->_richSnippets['offers'] =  array(
                "@type"         => "AggregateOffer",
                "availability"  => $this->_isInStock(),
                "priceCurrency" => Mage::app()->getStore()->getCurrentCurrencyCode(),
                "highPrice"     => Mage::helper('core')->currency($price_array['highPrice'], false, false),
                "lowPrice"      => Mage::helper('core')->currency($price_array['lowPrice'], false, false)

        );
    }


    /**
     * Save in richSnippets snippets data for bundle product
     *
     */
    protected function _bundleProduct() 
    {
        $array_price                    = Mage::getModel('bundle/product_price')->getPrices($this->_product);
        $this->_richSnippets            = $this->_productGlobalInfo();
        $this->_richSnippets['offers']  = array(
                "@type"             => "AggregateOffer",
                "priceCurrency"     => "REPLACE_CONTENT",
                "availability"      => $this->_isInStock(),
                "highPrice"         => Mage::helper('core')->currency($array_price[1], false, false),
                "lowPrice"          => Mage::helper('core')->currency($array_price[0], false, false)
        );

    }

    /**
     * Return the name, image, description, sku and brand of product
     *
     * @return array
     */
    protected function _productGlobalInfo() 
    {

        $arrayInfo = array(
            "@context"      => "http://schema.org/",
            "@type"         => "Product",
            "name"          => $this->_product->getName(),
            "image"         => $this->_product->getImageUrl(),
            "description"   => strip_tags($this->_product->getDescription()),
            "sku"           => $this->_product->getSku(),
            "mpn"           => $this->_product->getSupplierProductReference(),
            "brand"         => array(
                               "@type"  => "Thing",
                               "name"   => Mage::helper('catalog/output')->productAttribute($this->_product,$this->_product->getAttributeText('brand'), 'brand')
            )
        );
        return $arrayInfo;
    }

    /**
     * Look if the product is available
     *
     * @return string
     */
    protected function _isInStock() 
    {
        if (!$this->_product->isSaleable()){
            return "http://schema.org/PreOrder";
        }
        return "http://schema.org/InStock";

    }

    /**
     * Return the lowest and highest price for configurable product
     *
     * @return array
     */
    protected function _getLowestHighestPrice() 
    {

        $childProductIds    = Mage::getResourceSingleton('catalog/product_type_configurable')->getChildrenIds($this->_product->getId());
        $childProducts      = Mage::getResourceModel('catalog/product_collection')
            ->addIdFilter($childProductIds)
            ->addAttributeToSelect('price');

        $childPriceLowest   = "";
        $childPriceHighest  = "";
        foreach($childProducts as $child){
            if($child->isInStock()) {
                $this->_childProductsCount++;
            }
            if($childPriceLowest == '' || $childPriceLowest > $child->getPrice() )
                $childPriceLowest =  $child->getPrice();

            if($childPriceHighest == '' || $childPriceHighest < $child->getPrice() )
                $childPriceHighest =  $child->getPrice();

        }
        return array(
            'lowPrice'  => $childPriceLowest,
            'highPrice' => $childPriceHighest
        );
    }

    /**
     * If the product have rating, update $_richSnippets for add aggregateRating
     */
    protected function _addRating() 
    {
        if($this->_product->getRatingSummary() && $this->_product->getRatingSummary()->getReviewsCount()) {
            $this->_richSnippets['aggregateRating'] =  array(
                "@type"         => "AggregateRating",
                "ratingValue"   => $this->_product->getRatingSummary()->getRatingSummary(),
                "bestRating"    => "100",
                "worstRating"   => "0",
                "reviewCount"   => $this->_product->getRatingSummary()->getReviewsCount()
            );
        }
    }


    /**
     * Save all socials links in $_companyData
     */
    protected function _addSocialLinks() 
    {

        $this->_companyData['sameAs'] = array();
        $this->_companyData['sameAs'][] = 'REPLACE_CONTENT';

    }

    /**
     * get Search Box for google
     *
     * @return mixed
     */
    protected function _getSearchBox() {
        $searchBox = array(
            "@context"=> "http://schema.org",
              "@type"=> "WebSite",
              "name" => Mage::getStoreConfig('general/store_information/name'),
              "url"=> Mage::getBaseUrl(),
              "potentialAction"=> array(
                "@type"=> "SearchAction",
                "target"=> Mage::helper('catalogsearch')->getResultUrl() . '?q={search_term_string}',
                "query-input"=> "required name=search_term_string"
                )
        );

        return $searchBox;
    }


    public function getHomePageSnippets() 
    {
        $phone ='REPLACE_CONTENT';
        $this->_homepageData = $this->_getSearchBox();
        $this->_homepageData['about'] = array(
            "@type" => "Store",
            "name"  => Mage::getStoreConfig('general/store_information/name'),
            "url"   => Mage::getBaseUrl(),
            "logo"  => Mage::getDesign()->getSkinUrl(Mage::getStoreConfig('design/header/logo_src', Mage::app()->getStore()->getId())),
            "image" => Mage::getDesign()->getSkinUrl(Mage::getStoreConfig('design/header/logo_src', Mage::app()->getStore()->getId())),
            "priceRange" => 'REPLACE_CONTENT',
            "contactPoint" => array(
                "@type" => "ContactPoint",
                "telephone" => $phone,
                "contactType" => "customer service",
                "areaServed" => "REPLACE_CONTENT",
                "availableLanguage" => "REPLACE_CONTENT",
                "hoursAvailable" => array(
                    "@type" => "OpeningHoursSpecification",
                    "opens" => "REPLACE_CONTENT",
                    "closes" => "REPLACE_CONTENT",
                    "dayOfWeek" => array(
                        array(
                            "@type" => "DayOfWeek",
                            "name" => "http://purl.org/goodrelations/v1#Monday"
                        ),
                        array(
                            "@type" => "DayOfWeek",
                            "name" => "http://purl.org/goodrelations/v1#Tuesday"
                        ),
                        array(
                            "@type" => "DayOfWeek",
                            "name" => "http://purl.org/goodrelations/v1#Wednesday"
                        ),
                        array(
                            "@type" => "DayOfWeek",
                            "name" => "http://purl.org/goodrelations/v1#Thursday"
                        ),
                        array(
                            "@type" => "DayOfWeek",
                            "name" => "http://purl.org/goodrelations/v1#Friday"
                        )
                    )
                )
            ),
            "address" => array(
                "@type" => "PostalAddress",
                "streetAddress" => "REPLACE_CONTENT",
                "addressLocality" => "REPLACE_CONTENT",
                "postalCode" => "REPLACE_CONTENT",
                "addressCountry" => "REPLACE_CONTENT",
                "telephone" => $phone
            )
        );
        $this->_addSocialLinks();

        return Mage::helper('core')->jsonEncode($this->_homepageData);
    }
}
