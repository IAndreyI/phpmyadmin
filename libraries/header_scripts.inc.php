<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 *
 * @package PhpMyAdmin
 */
if (! defined('PHPMYADMIN')) {
    exit;
}

// Cross-framing protection
if ( false === $GLOBALS['cfg']['AllowThirdPartyFraming']) {
    echo PMA_includeJS('cross_framing_protection.js');
}
// generate title (unless we already have $page_title, from cookie auth)
if (! isset($page_title)) {
    if ($GLOBALS['server'] > 0) {
        if (! empty($GLOBALS['table'])) {
            $temp_title = $GLOBALS['cfg']['TitleTable'];
        } else if (! empty($GLOBALS['db'])) {
            $temp_title = $GLOBALS['cfg']['TitleDatabase'];
        } elseif (! empty($GLOBALS['cfg']['Server']['host'])) {
            $temp_title = $GLOBALS['cfg']['TitleServer'];
        } else {
            $temp_title = $GLOBALS['cfg']['TitleDefault'];
        }
        $title = PMA_expandUserString($temp_title);
    }
} else {
    $title = $page_title;
}
// here, the function does not exist with this configuration:
// $cfg['ServerDefault'] = 0;
$is_superuser = function_exists('PMA_isSuperuser') && PMA_isSuperuser();

$GLOBALS['js_include'][] = 'functions.js';
$GLOBALS['js_include'][] = 'jquery/jquery.qtip-1.0.0-rc3.js';
if ($GLOBALS['cfg']['CodemirrorEnable']) {
    $GLOBALS['js_include'][] = 'codemirror/lib/codemirror.js';
    $GLOBALS['js_include'][] = 'codemirror/mode/mysql/mysql.js';
}

$params = array('lang' => $GLOBALS['lang']);
if (isset($GLOBALS['db'])) {
    $params['db'] = $GLOBALS['db'];
}
$GLOBALS['js_include'][] = 'messages.php' . PMA_generate_common_url($params);
// Append the theme id to this url to invalidate the cache on a theme change
$GLOBALS['js_include'][] = 'get_image.js.php?theme='
    . urlencode($_SESSION['PMA_Theme']->getId());

/**
 * Here we add a timestamp when loading the file, so that users who
 * upgrade phpMyAdmin are not stuck with older .js files in their
 * browser cache. This produces an HTTP 304 request for each file.
 */

// avoid loading twice a js file
$GLOBALS['js_include'] = array_unique($GLOBALS['js_include']);
foreach ($GLOBALS['js_include'] as $js_script_file) {
    $ie_conditional = false;
    if (is_array($js_script_file)) {
        list($js_script_file, $ie_conditional) = $js_script_file;
    }
    echo PMA_includeJS($js_script_file, $ie_conditional);
}

$title_to_set = isset($title)
    ? PMA_sanitize(PMA_escapeJsString($title), false, true)
    : '';
// Below javascript Updates the title of the frameset if possible
?>
<script type="text/javascript">
// <![CDATA[
if (typeof(parent.document) != 'undefined' && typeof(parent.document) != 'unknown'
    && typeof(parent.document.title) == 'string') {
    parent.document.title = '<?php echo $title_to_set; ?>';
}
<?php
if (count($GLOBALS['js_script']) > 0) {
    echo implode("\n", $GLOBALS['js_script'])."\n";
}

foreach ($GLOBALS['js_events'] as $js_event) {
    echo "$(window.parent).bind('" . $js_event['event'] . "', "
        . $js_event['function'] . ");\n";
}
?>
// ]]>
</script>
<?php
// Reloads the navigation frame via JavaScript if required
echo PMA_getReloadNavigationScript();

?>
