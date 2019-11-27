Ligth way for add basic Rich Snippets on your Magento.

# Getting Start
1/ Copy this Helper on your local module

2/ Replace Helper class name by your module name

3/ Replace All 'REPLACE_VALUE' by your value

4/ Call this method on your homepage :
```
<script type="application/ld+json">
    <?php echo Mage::helper('YOUR_MODULE/snippets')->getHomePageSnippets(); ?>
</script>
```
5/ Call this method on your product page :
```
<script type="application/ld+json">
    <?php echo Mage::helper('YOUR_MODULE/snippets')->getProductRichSnippets($_product); ?>
</script>
```
6/ Valid your data with google testing tool : https://search.google.com/structured-data/testing-tool/u/0/

And... It's done !
(This Helper it's not complete, you can contribute ;))

# Snippets adds
### Product types 
- Simple
- Configurable
- Bundle
### Snippets type
- AggregateOffer 
    - Availability (In stock / Out of stock)
    - Price currency
    - High / Low / Current Price
- Product
    - Name
    - Image
    - Description
    - SKU
    - MPN
    - Brand
- AggregateRating
    - Rating value
    - Best rating
    - Worst rating
    - Review count
- Website
    - Name
    - Url
    - SearchAction
    - Logo
    - Area served
    - Contact type
    - Hours available
    - Address
    - Social Links
