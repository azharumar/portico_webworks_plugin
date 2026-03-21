<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pw_fact_sheet_page_block_markup() {
	return <<<'PW_FACT_BLOCKS'
<!-- wp:generateblocks/element {"uniqueId":"pwfse0","tagName":"div","styles":[],"css":""} -->
<div class="gb-element gb-element-pwfse0">
<!-- wp:group {"className":"pw-fact-sheet","layout":{"type":"constrained"}} -->
<div class="wp-block-group pw-fact-sheet is-layout-constrained wp-block-group-is-layout-constrained">
<!-- wp:paragraph -->
<p>{{portico:pw_fact_error}}</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">{{portico:pw_fact_title}}</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>{{portico:pw_fact_lead}}</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Contents</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list">
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
</ul>
<!-- /wp:list -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Overview</h2>
<!-- /wp:heading -->

<!-- wp:html -->
{{portico:pw_fact_header}}
<!-- /wp:html -->

<!-- wp:html -->
{{portico:pw_fact_property}}
<!-- /wp:html -->

<!-- wp:html -->
{{portico:pw_fact_linked}}
<!-- /wp:html -->

</div>
<!-- /wp:group -->
</div>
<!-- /wp:generateblocks/element -->
PW_FACT_BLOCKS;
}
