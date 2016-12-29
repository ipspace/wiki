<?php
# tags.php
#
# ipSpace tags extensions:
#
# - xh1 ... xh4: alternate header tags that prevent entries from being inserted into TOC
#

namespace ipSpace;

if( !defined( 'MEDIAWIKI' ) ) {
        echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
        die( -1 );
}

global $wgExtensionCredits, $wgHooks;

$wgExtensionCredits['parserhook'][] = array(
        'name'         => 'ipSpace.net tags',
        'version'      => '1.0',
        'author'       => 'Ivan Pepelnjak',
        'url'          => 'http://github.com/ipspace/mediawiki',
        'description'  => 'The extension provides alternate HTML heading tags that bypass the ToC generation'
);

$wgHooks['ParserFirstCallInit'][] = 'ipSpace\extraTags::onParserSetup';

global $wgAllowDisplayTitle, $wgRestrictDisplayTitle;

$wgAllowDisplayTitle = true;
$wgRestrictDisplayTitle = false;

class extraTags {

    function onParserSetup() {
        global $wgParser;

        $wgParser->setHook( 'xh1', 'ipSpace\extraTags::efSidebarHeadingRenderH1' );
        $wgParser->setHook( 'xh2', 'ipSpace\extraTags::efSidebarHeadingRenderH2' );
        $wgParser->setHook( 'xh3', 'ipSpace\extraTags::efSidebarHeadingRenderH3' );
        $wgParser->setHook( 'xh4', 'ipSpace\extraTags::efSidebarHeadingRenderH4' );
        return true;
    }

    function headerTagsCommon($tag,$input,$argv,$parser) {
        return "<".$tag.">".$parser->recursiveTagParse($input)."</".$tag.">";
    }

    # The callback function for converting the input text to HTML output
    function efSidebarHeadingRenderH1( $input, $argv, $parser ) {
        return extraTags::headerTagsCommon("h1",$input,$argv,$parser);
    }

    function efSidebarHeadingRenderH2( $input, $argv, $parser ) {
        return extraTags::headerTagsCommon("h2",$input,$argv,$parser);
    }

    function efSidebarHeadingRenderH3( $input, $argv, $parser ) {
        return extraTags::headerTagsCommon("h3",$input,$argv,$parser);
    }

    function efSidebarHeadingRenderH4( $input, $argv, $parser ) {
        return extraTags::headerTagsCommon("h4",$input,$argv,$parser);
    }
}

$wgHooks['CategoryViewer::generateLink'][] = function($type, $title, $html, &$link) {
    if ($type != 'page') return;
    $link = \Linker::link( $title, $title->getText() );
};

?>