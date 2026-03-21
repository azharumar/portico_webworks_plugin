<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pw_fact_sheet_page_block_markup() {
	return <<<'PW_FACT_BLOCKS'
<!-- wp:generateblocks/element {"uniqueId":"pwfse0","tagName":"div","styles":[],"css":""} -->
<div class="gb-element gb-element-pwfse0">
<!-- wp:generateblocks/text {"uniqueId":"pwfsterr","tagName":"p","styles":[],"css":"","blockVersion":4} -->
<p class="gb-text gb-text-pwfsterr">{{portico:pw_fact_error}}</p>
<!-- /wp:generateblocks/text -->

<!-- wp:generateblocks/headline {"uniqueId":"pwfsh1","element":"h1","styles":[],"css":"","blockVersion":4} -->
<h1 class="gb-headline gb-headline-pwfsh1"><span class="gb-headline-text">{{portico:pw_fact_title}}</span></h1>
<!-- /wp:generateblocks/headline -->

<!-- wp:generateblocks/text {"uniqueId":"pwfstlead","tagName":"p","styles":[],"css":"","blockVersion":4} -->
<p class="gb-text gb-text-pwfstlead">{{portico:pw_fact_lead}}</p>
<!-- /wp:generateblocks/text -->

<!-- wp:generateblocks/headline {"uniqueId":"pwfsh2","element":"h2","styles":[],"css":"","blockVersion":4} -->
<h2 class="gb-headline gb-headline-pwfsh2"><span class="gb-headline-text">Contents</span></h2>
<!-- /wp:generateblocks/headline -->

<!-- wp:generateblocks/text {"uniqueId":"pwfstoc","tagName":"div","styles":[],"css":"","blockVersion":4} -->
<div class="gb-text gb-text-pwfstoc"><ul class="wp-block-list">
<li><a href="#pw-fact-property-heading">Property details</a></li>
<li><a href="#pw-fact-pw-room-type">Room types</a></li>
<li><a href="#pw-fact-pw-restaurant">Restaurants</a></li>
<li><a href="#pw-fact-pw-spa">Spas</a></li>
<li><a href="#pw-fact-pw-meeting-room">Meeting rooms</a></li>
<li><a href="#pw-fact-pw-amenity">Amenities</a></li>
<li><a href="#pw-fact-pw-policy">Policies</a></li>
<li><a href="#pw-fact-pw-nearby">Nearby places</a></li>
<li><a href="#pw-fact-pw-event">Events</a></li>
<li><a href="#pw-fact-pw-experience">Experiences</a></li>
<li><a href="#pw-fact-pw-faq">FAQs</a></li>
<li><a href="#pw-fact-pw-offer">Offers</a></li>
</ul></div>
<!-- /wp:generateblocks/text -->

<!-- wp:generateblocks/headline {"uniqueId":"pwfsh3","element":"h2","styles":[],"css":"","blockVersion":4} -->
<h2 class="gb-headline gb-headline-pwfsh3"><span class="gb-headline-text">Overview</span></h2>
<!-- /wp:generateblocks/headline -->

<!-- wp:generateblocks/text {"uniqueId":"pwfsthdr","tagName":"div","styles":[],"css":"","blockVersion":4} -->
<div class="gb-text gb-text-pwfsthdr">{{portico:pw_fact_header}}</div>
<!-- /wp:generateblocks/text -->

<!-- wp:generateblocks/text {"uniqueId":"pwfstprp","tagName":"div","styles":[],"css":"","blockVersion":4} -->
<div class="gb-text gb-text-pwfstprp">{{portico:pw_fact_property}}</div>
<!-- /wp:generateblocks/text -->

<!-- wp:generateblocks/text {"uniqueId":"pwfstlnk","tagName":"div","styles":[],"css":"","blockVersion":4} -->
<div class="gb-text gb-text-pwfstlnk">{{portico:pw_fact_linked}}</div>
<!-- /wp:generateblocks/text -->

</div>
<!-- /wp:generateblocks/element -->
PW_FACT_BLOCKS;
}
