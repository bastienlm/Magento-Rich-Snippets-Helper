# Getting Start
1/ Copy this Helper on your local module

2/ Replace Helper class name by your module name

3/ Replace All 'REPLACE_VALUE' by your value

4/ Call this method on your homepage :
```
<script type="application/ld+json">
    <?php echo Mage::helper('rshop_local/snippets')->getHomePageSnippets(); ?>
</script>
```
5/ Call this method on your product page :
```
<script type="application/ld+json">
    <?php echo Mage::helper('rshop_local/snippets')->getProductRichSnippets($_product); ?>
</script>
```
6/ Valid your data with google testing tool : https://search.google.com/structured-data/testing-tool/u/0/

And... It's done !
(This Helper it's not complete, you can contribute ;))

