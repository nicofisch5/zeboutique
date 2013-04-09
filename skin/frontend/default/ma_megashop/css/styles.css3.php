<?php
    header('Content-type: text/css; charset: UTF-8');
    header('Cache-Control: must-revalidate');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
    $url = $_REQUEST['url'];
?>

.products-grid .item-inner:hover,
.ma-newproductslider-container .flexslider .slides > li .item-inner:hover, .grid-related .products-grid .item:hover,
.products-list li.item:hover
{
	-moz-box-shadow: 0 0 3px 0 #ccc;
	-webkit-box-shadow: 0 0 3px 0 #ccc;
	box-shadow: 0 0 3px 0 #ccc;
}

.products-grid .item-inner:hover, .ma-mostviewed-products .popular-inner:hover {
	-moz-box-shadow: 0 0 3px 0 #ccc;
	-webkit-box-shadow: 0 0 3px 0 #ccc;
	box-shadow: 0 0 3px 0 #ccc;
}
button.btn-cart span,
.products-grid .actions .link-wishlist,
.ma-newproductslider-container .flexslider .slides > li .item-inner .actions .link-wishlist,
.ma-newproductslider-container .flex-direction-nav a,
.ma-featured-vertscroller-wrap .jcarousel-next-vertical, 
.ma-featured-vertscroller-wrap  .jcarousel-prev-vertical,
#nav a,
.ma-thumbnail-container .flex-direction-nav a,
.ma-banner7-container .flex-control-paging li a,
#back-top,
.product-prev,
.product-next
{
	-webkit-transition: 0.5s;
	-moz-transition: 0.5s;
	transition: 0.5s;
}
#back-top
{
	-webkit-border-radius: 24px;
	-moz-border-radius: 24px;
	border-radius: 24px;
	behavior: url(<?php echo $url; ?>css/css3.htc);
}

.ma-newproductslider-container .label-box
{
	-webkit-border-radius: 50px;
	-moz-border-radius: 50px;
	border-radius: 50px;
	behavior: url(<?php echo $url; ?>css/css3.htc);
}