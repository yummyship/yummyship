<!DOCTYPE html>
<html>
<head>
<title><?php echo $metaInfo->title; ?></title>
<meta charset="UTF-8">
<?php if( $current <> '404' ) {
if ($metaInfo->keywords) {?>
<meta name="keywords" content="<?php echo $metaInfo->keywords; ?>">
<?php }
if ($metaInfo->description) {?>
<meta name="description" content="<?php echo $metaInfo->description; ?>">
<?php }}?>
<link rel="shortcut icon" type="image/x-icon" href="<?php echo BYENDS_THEMES_STATIC_URL; ?>favicon.ico" />
<link rel="stylesheet" type="text/css" href="<?php echo BYENDS_THEMES_STATIC_URL; ?>yummyship.css?<?php echo $ver; ?>" />
</head>
<body>