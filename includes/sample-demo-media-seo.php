<?php
/**
 * SEO text for sideloaded assets/sample-media attachments (title, alt, description, caption).
 *
 * @package Portico_Webworks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filename (as on disk) => [ 'title', 'alt', 'description', 'caption' ].
 *
 * @return array<string, array{title: string, alt: string, description: string, caption: string}>
 */
function pw_sample_get_sample_media_seo_map() {
	static $map = null;
	if ( $map !== null ) {
		return $map;
	}
	$map = [
		'deluxe-king-room.jpeg' => [
			'title'       => 'Deluxe king hotel room with city view',
			'alt'         => 'Deluxe king guest room with king bed, seating and city outlook',
			'description' => 'Showcases a deluxe king category room with a comfortable work zone and soft lighting. Use this asset for room-type galleries and booking landing pages.',
			'caption'     => 'Deluxe king room — restful sleep and space to work.',
		],
		'room-pool-villa.jpeg' => [
			'title'       => 'Garden pool villa exterior and terrace',
			'alt'         => 'Resort pool villa with private terrace and tropical planting',
			'description' => 'Highlights a villa product with outdoor living and greenery. Suitable for luxury room merchandising and resort overview content.',
			'caption'     => 'Garden pool villa with a private outdoor corner.',
		],
		'rooms-pool-villa-02.jpeg' => [
			'title'       => 'Pool villa bedroom with natural light',
			'alt'         => 'Bright bedroom in a pool villa with layered textures and daylight',
			'description' => 'Interior view of a villa bedroom emphasising calm materials and natural light. Pairs with exterior villa shots in carousels.',
			'caption'     => 'Villa bedroom designed for quiet, airy comfort.',
		],
		'rooms-twin-room.png' => [
			'title'       => 'Premier twin hotel room with two beds',
			'alt'         => 'Hotel twin room with two single beds and window light',
			'description' => 'Twin-bed layout for families and sharing guests. Supports parity room pages and OTA-style room cards.',
			'caption'     => 'Premier twin — ideal for friends or family stays.',
		],
		'rooms-swuite-living-room.jpeg' => [
			'title'       => 'Executive suite separate living area',
			'alt'         => 'Suite living room with sofa, chairs and lounge lighting',
			'description' => 'Living zone of an executive suite for longer stays and entertaining. Complements bedroom and bathroom suite imagery.',
			'caption'     => 'Suite living room for relaxing or hosting.',
		],
		'rooms-balcony.jpeg' => [
			'title'       => 'Guest room private balcony with a view',
			'alt'         => 'Hotel room balcony with seating overlooking pool or sea',
			'description' => 'Outdoor space attached to a guest room; works for sea-facing or resort-pool narratives.',
			'caption'     => 'Your private balcony for morning coffee or sunset.',
		],
		'rooms-work-desk.jpeg' => [
			'title'       => 'In-room work desk and ergonomic chair',
			'alt'         => 'Guest room writing desk with task lighting and ergonomic chair',
			'description' => 'Business-traveller amenity shot for bleisure and corporate RFP pages.',
			'caption'     => 'Dedicated workspace when you need to stay productive.',
		],
		'rooms-bathroom.jpeg' => [
			'title'       => 'Hotel bathroom with walk-in shower',
			'alt'         => 'Modern hotel bathroom with marble or tile and glass shower',
			'description' => 'Primary bathroom hero for room types emphasising premium fittings.',
			'caption'     => 'Spa-inspired bathroom with a refreshing walk-in shower.',
		],
		'rooms-bathroom-02.jpeg' => [
			'title'       => 'Bright guest bathroom with modern fixtures',
			'alt'         => 'Second bathroom style with vanity and contemporary fittings',
			'description' => 'Alternate bathroom look for twin rooms or sea-facing categories.',
			'caption'     => 'Clean, bright bathroom ready for your stay.',
		],
		'hotel-exterior-areal-view.jpeg' => [
			'title'       => 'Aerial view of resort hotel and grounds',
			'alt'         => 'Drone-style aerial of hotel buildings, gardens and pool',
			'description' => 'Property-scale hero for home pages, social sharing and Open Graph previews.',
			'caption'     => 'Resort scale from above — gardens, pool and architecture.',
		],
		'hotel-exterior-porch.jpeg' => [
			'title'       => 'Hotel arrival porch and porte-cochère',
			'alt'         => 'Hotel entrance drive with covered drop-off and landscaping',
			'description' => 'First-impression exterior for arrival storytelling and maps/directions content.',
			'caption'     => 'A welcoming arrival at the porte-cochère.',
		],
		'hotel-interior-corridoor.jpeg' => [
			'title'       => 'Hotel guest corridor with warm lighting',
			'alt'         => 'Interior hotel hallway with soft lighting and room doors',
			'description' => 'Wayfinding and atmosphere shot for property tours and accessibility pages.',
			'caption'     => 'Calm corridors leading to your room.',
		],
		'lobby-01.jpeg' => [
			'title'       => 'Hotel lobby with reception desk',
			'alt'         => 'Spacious lobby with reception, seating and arrival area',
			'description' => 'Core lobby visual for brand and service messaging.',
			'caption'     => 'Lobby and reception — your first stop after arrival.',
		],
		'lobby-common space.jpeg' => [
			'title'       => 'Lobby lounge and circulation space',
			'alt'         => 'Hotel lobby lounge seating and open circulation',
			'description' => 'Secondary lobby angle showing flow and informal seating.',
			'caption'     => 'Lobby lounge for meeting or unwinding.',
		],
		'interior-lift-landing-area.jpeg' => [
			'title'       => 'Guest floor lift lobby',
			'alt'         => 'Elevator landing on a guest room floor with signage',
			'description' => 'Supports multi-floor properties and wayfinding copy.',
			'caption'     => 'Lift lobby on the guest room floors.',
		],
		'indoor-gym.jpeg' => [
			'title'       => 'Hotel fitness centre with cardio equipment',
			'alt'         => 'Gym with treadmills, weights and mirrors',
			'description' => 'Wellness amenity for leisure and corporate guests.',
			'caption'     => 'Fitness centre open for your daily workout.',
		],
		'indoor-pool.jpeg' => [
			'title'       => 'Indoor swimming pool at the hotel',
			'alt'         => 'Indoor pool deck with water, loungers and lighting',
			'description' => 'Year-round swimming narrative; reusable across properties in demo data.',
			'caption'     => 'Swim laps or unwind by the indoor pool.',
		],
		'restaurant-all-day-dining.jpeg' => [
			'title'       => 'All-day dining restaurant interior',
			'alt'         => 'Restaurant dining room with tables set for service',
			'description' => 'F&B outlet hero for buffet and à la carte positioning.',
			'caption'     => 'All-day dining from breakfast through dinner.',
		],
		'raustaurant-all-day-dining-02.jpeg' => [
			'title'       => 'Spacious all-day dining seating area',
			'alt'         => 'Wide angle of restaurant seating and service stations',
			'description' => 'Second angle for galleries and event-capacity storytelling.',
			'caption'     => 'Room for groups and quiet tables alike.',
		],
		'restaruant-private-dining-room.jpeg' => [
			'title'       => 'Private dining room for celebrations',
			'alt'         => 'Enclosed private dining with set table and soft lighting',
			'description' => 'MICE and celebration upsell; matches private dining RFPs.',
			'caption'     => 'Private dining for birthdays, anniversaries and board dinners.',
		],
		'restaurant-breakfast-buffet.jpeg' => [
			'title'       => 'Breakfast buffet presentation',
			'alt'         => 'Morning buffet with continental and hot selections',
			'description' => 'Morning F&B merchandising and festive brunch campaigns.',
			'caption'     => 'Start the day from a generous breakfast buffet.',
		],
		'restaurant-bar.jpeg' => [
			'title'       => 'Hotel bar and lounge seating',
			'alt'         => 'Bar counter with bottles, glassware and lounge seating',
			'description' => 'Evening F&B and cocktail programme support.',
			'caption'     => 'Cocktails and conversation at the bar.',
		],
		'restaurant-roof-top.jpeg' => [
			'title'       => 'Rooftop restaurant dining with a view',
			'alt'         => 'Rooftop dining tables with city or sky outlook',
			'description' => 'Signature outlet positioning for skyline dining.',
			'caption'     => 'Dine above the city on the rooftop.',
		],
		'spa-treatment-room.jpeg' => [
			'title'       => 'Spa treatment room with massage table',
			'alt'         => 'Calm spa room with treatment bed, towels and decor',
			'description' => 'Core spa merchandising for menu and package pages.',
			'caption'     => 'Treatment room prepared for your spa journey.',
		],
		'spas-reception.jpeg' => [
			'title'       => 'Spa reception and welcome lounge',
			'alt'         => 'Spa front desk with seating and retail display',
			'description' => 'Arrival experience for wellness journeys.',
			'caption'     => 'Check in and unwind at spa reception.',
		],
		'spas-couple-therapy-room.jpeg' => [
			'title'       => 'Couples spa therapy suite',
			'alt'         => 'Dual massage setup for couples treatments',
			'description' => 'Romance and celebration spa packages.',
			'caption'     => 'Side-by-side rituals for two.',
		],
		'steam-room.jpeg' => [
			'title'       => 'Spa steam or thermal facility',
			'alt'         => 'Steam room benches and tiled interior',
			'description' => 'Thermal journey and pre/post treatment relaxation.',
			'caption'     => 'Warm up and detox in the steam area.',
		],
		'meeting-gala-dinner.jpeg' => [
			'title'       => 'Ballroom gala dinner setup',
			'alt'         => 'Banquet rounds with centrepieces and stage lighting',
			'description' => 'Events and weddings revenue narrative.',
			'caption'     => 'Gala-ready ballroom for awards and celebrations.',
		],
		'meeting-theatre-style-seating.jpeg' => [
			'title'       => 'Conference theatre-style seating',
			'alt'         => 'Rows of chairs facing stage in a meeting venue',
			'description' => 'Large-delegate conferences and keynotes.',
			'caption'     => 'Theatre layout for plenaries and launches.',
		],
		'meeting-classroom-style-seating.jpeg' => [
			'title'       => 'Training room classroom table layout',
			'alt'         => 'Tables and chairs in classroom formation with AV',
			'description' => 'Workshops, academies and corporate training.',
			'caption'     => 'Classroom style for learning and collaboration.',
		],
		'meeting-wedding-round-table.jpeg' => [
			'title'       => 'Wedding banquet round tables',
			'alt'         => 'Decorated round tables for wedding reception',
			'description' => 'Social events and wedding brochure imagery.',
			'caption'     => 'Round tables dressed for your celebration.',
		],
		'meeting-board-room.jpeg' => [
			'title'       => 'Executive boardroom with conference table',
			'alt'         => 'Boardroom with long table, chairs and daylight',
			'description' => 'C-suite and small meeting sales.',
			'caption'     => 'Boardroom privacy for decisive meetings.',
		],
		'meeting-u-shape-boardroom-setup.jpeg' => [
			'title'       => 'U-shape meeting room layout',
			'alt'         => 'Tables in U formation with presentation screen',
			'description' => 'Interactive sessions and board workshops.',
			'caption'     => 'U-shape setup for dialogue and visibility.',
		],
		'experience-01.jpeg' => [
			'title'       => 'Curated guest experience or local activity',
			'alt'         => 'Lifestyle image representing a hotel-curated experience',
			'description' => 'Generic experience placeholder for demo carousels when a bespoke shot is not required.',
			'caption'     => 'Discover experiences curated by the hotel.',
		],
		'experience-craft-beer-food-walk-bengaluru.jpeg' => [
			'title'       => 'Craft beer and food walk in Bengaluru',
			'alt'         => 'Evening street or venue scene for a craft beer food tour',
			'description' => 'Local immersion experience for Bengaluru leisure positioning.',
			'caption'     => 'Taste Bengaluru — craft beer and bites after dark.',
		],
		'experience-morning-yoga-session.jpeg' => [
			'title'       => 'Morning yoga session for hotel guests',
			'alt'         => 'Yoga mats and guests in an outdoor or rooftop session',
			'description' => 'Wellness programming and sunrise rituals.',
			'caption'     => 'Greet the day with a guided yoga session.',
		],
		'experience-bengaluru-heritage-city-tour.jpeg' => [
			'title'       => 'Bengaluru heritage city tour experience',
			'alt'         => 'Historic architecture or landmark on a city heritage route',
			'description' => 'Cultural tourism add-on for city hotels.',
			'caption'     => 'Walk Bengaluru’s heritage with expert context.',
		],
		'experience-whisky-masterclass-spice-verandah.jpeg' => [
			'title'       => 'Whisky masterclass at Spice Verandah',
			'alt'         => 'Whisky tasting setup with glasses and bottles',
			'description' => 'F&B-led experience tied to the all-day dining outlet.',
			'caption'     => 'Guided pours and stories in a whisky masterclass.',
		],
		'experience-sunrise-kayaking-goa.jpeg' => [
			'title'       => 'Sunrise kayaking along the Goa coast',
			'alt'         => 'Kayaks on calm water at dawn with coastal light',
			'description' => 'Adventure and romance for Goa resort guests.',
			'caption'     => 'Paddle into sunrise on the Arabian Sea.',
		],
		'experience-cooking-class-by-chef.jpeg' => [
			'title'       => 'Hands-on cooking class with hotel chef',
			'alt'         => 'Kitchen or demo counter with ingredients and chef',
			'description' => 'Culinary tourism and family activities.',
			'caption'     => 'Cook regional favourites alongside our chef.',
		],
		'experience-spice-plantation-half-day-tour.jpeg' => [
			'title'       => 'Spice plantation half-day tour',
			'alt'         => 'Tropical plantation path with spice plants',
			'description' => 'Nature and flavour education excursion.',
			'caption'     => 'Walk the plantation — scent, taste and terroir.',
		],
		'experience-dolphin-watching-boat-trip.jpeg' => [
			'title'       => 'Dolphin watching boat trip',
			'alt'         => 'Boat on open water for marine wildlife viewing',
			'description' => 'Coastal wildlife experience from Goa base.',
			'caption'     => 'Scan the horizon for dolphins on a boat outing.',
		],
		'experience-full-moon-beach-bonfire-dinner.jpeg' => [
			'title'       => 'Full moon beach bonfire dinner',
			'alt'         => 'Beach setup with fire glow and dining under moonlight',
			'description' => 'Signature night experience for resort storytelling.',
			'caption'     => 'Dine by bonfire light beneath the full moon.',
		],
		'event-diwali-gala-rooftop-restaurant.jpeg' => [
			'title'       => 'Diwali gala dinner on a rooftop restaurant',
			'alt'         => 'Festive rooftop dining with lights and skyline',
			'description' => 'Seasonal event merchandising for Indian festivals.',
			'caption'     => 'Diwali celebrations above the city lights.',
		],
		'event-new-years-eve-ballroom-bengaluru.jpeg' => [
			'title'       => 'New Year’s Eve countdown in a Bengaluru ballroom',
			'alt'         => 'Ballroom party setup with stage and lighting',
			'description' => 'Year-end event sales for city hotels.',
			'caption'     => 'Ring in the new year in the grand ballroom.',
		],
		'event-new-years-eve-beach-party-goa-night.jpeg' => [
			'title'       => 'New Year’s Eve beach party at night',
			'alt'         => 'Night beach scene with party lighting and crowd energy',
			'description' => 'Goa nightlife and NYE campaign imagery.',
			'caption'     => 'Beachside countdown under the stars.',
		],
		'offer-promo-advance-purchase-bengaluru.jpeg' => [
			'title'       => 'Advance purchase offer — Bengaluru stay',
			'alt'         => 'Hotel lifestyle image promoting advance booking savings',
			'description' => 'Promotional creative for prepaid rate campaigns in Bengaluru.',
			'caption'     => 'Book early and save on your Bengaluru stay.',
		],
		'offer-package-business-bengaluru.jpeg' => [
			'title'       => 'Business travel package — Bengaluru',
			'alt'         => 'Professional traveller context for corporate hotel package',
			'description' => 'Bleisure and corporate package landing pages.',
			'caption'     => 'Business stays with the extras that matter.',
		],
		'offer-promo-early-bird-goa.jpeg' => [
			'title'       => 'Early bird promotion — Goa escape',
			'alt'         => 'Resort or beach mood image for early booking promo',
			'description' => 'Lead-time discount creative for Goa properties.',
			'caption'     => 'Plan ahead — early bird rates for Goa.',
		],
		'offer-package-honeymoon-goa.jpeg' => [
			'title'       => 'Honeymoon package by the sea — Goa',
			'alt'         => 'Romantic coastal scene for honeymoon offer',
			'description' => 'Romance and celebration upsell creative.',
			'caption'     => 'Honeymoon moments on the Goa coast.',
		],
		'offer-promo-festive-goa-christmas.jpeg' => [
			'title'       => 'Festive Christmas and New Year — Goa',
			'alt'         => 'Festive dining or decor suggesting holiday season',
			'description' => 'Seasonal packaging for December holidays.',
			'caption'     => 'Festive Goa — Christmas brunch to NYE beach nights.',
		],
		'nearby-airport.jpeg' => [
			'title'       => 'Airport terminal and travel connectivity',
			'alt'         => 'Modern airport exterior or terminal for guest directions',
			'description' => 'Nearby place content for arrival and transfer copy.',
			'caption'     => 'Easy connections via the international airport.',
		],
		'nearby-mg-road-metro-station-bengaluru.jpeg' => [
			'title'       => 'MG Road metro station — Bengaluru',
			'alt'         => 'Metro entrance or signage in central Bengaluru',
			'description' => 'Transit access for city-centre hotels.',
			'caption'     => 'MG Road on the metro — minutes from the hotel.',
		],
		'nearby-ub-city-mall-bengaluru.jpeg' => [
			'title'       => 'UB City luxury mall — Bengaluru',
			'alt'         => 'Upscale mall facade or plaza Bengaluru',
			'description' => 'Retail and dining proximity for luxury positioning.',
			'caption'     => 'Luxury shopping and dining at UB City.',
		],
		'nearby-lido-mall-bengaluru.jpeg' => [
			'title'       => 'Lido Mall — Bengaluru',
			'alt'         => 'Shopping mall exterior or entrance Lido Bengaluru',
			'description' => 'Local retail anchor for neighbourhood pages.',
			'caption'     => 'Retail therapy a short ride away at Lido Mall.',
		],
		'nearby-park.jpeg' => [
			'title'       => 'Urban park and green space near the hotel',
			'alt'         => 'Tree-lined park paths and lawns',
			'description' => 'Wellness walks and jogging routes for guests.',
			'caption'     => 'Morning walks in the park next door.',
		],
		'nearby-st-marks-cathedral-bengaluru.jpeg' => [
			'title'       => 'St. Mark’s Cathedral — Bengaluru',
			'alt'         => 'Historic church architecture St Marks Bengaluru',
			'description' => 'Heritage sightseeing from a city hotel base.',
			'caption'     => 'Colonial-era landmark — St. Mark’s Cathedral.',
		],
		'nearby-bangalore-palace-bengaluru.jpeg' => [
			'title'       => 'Bangalore Palace — Bengaluru',
			'alt'         => 'Palace facade and grounds Bangalore Palace',
			'description' => 'Iconic city attraction for itinerary builders.',
			'caption'     => 'Royal flair at Bangalore Palace.',
		],
		'nearby-shopping-mall.jpeg' => [
			'title'       => 'Shopping mall near the hotel',
			'alt'         => 'Contemporary mall exterior with glass and signage',
			'description' => 'Generic retail proximity when a specific mall is not required.',
			'caption'     => 'Malls, cinema and dining within easy reach.',
		],
		'nearby-historical-place.jpeg' => [
			'title'       => 'Historic site near the property',
			'alt'         => 'Heritage building or monument in soft daylight',
			'description' => 'Flexible heritage placeholder for nearby listings.',
			'caption'     => 'History and architecture on your doorstep.',
		],
		'nearby-beach.jpeg' => [
			'title'       => 'Sandy beach and coastline',
			'alt'         => 'Wide beach with sand, sea and horizon',
			'description' => 'Coastal proximity for resort and drive-market copy.',
			'caption'     => 'Sun, sand and sea a short hop away.',
		],
		'nearby-baga-beach-goa.jpeg' => [
			'title'       => 'Baga Beach — North Goa',
			'alt'         => 'Baga Beach shoreline with palms and visitors',
			'description' => 'Named beach for Goa resort neighbourhood pages.',
			'caption'     => 'Baga’s energy — beach shacks and sunsets.',
		],
		'nearby-goa-international-airport-terminal.jpeg' => [
			'title'       => 'Goa international airport terminal',
			'alt'         => 'Airport terminal exterior or concourse Goa',
			'description' => 'Arrival logistics for Goa resort guests.',
			'caption'     => 'Land in Goa — terminal to resort made simple.',
		],
		'nearby-saturday-night-market-arpora.jpeg' => [
			'title'       => 'Saturday Night Market — Arpora, Goa',
			'alt'         => 'Night market stalls lights and crowd Arpora',
			'description' => 'Nightlife and shopping itinerary content.',
			'caption'     => 'Arpora’s Saturday market — food, music and finds.',
		],
		'nearby-basilica-bom-jesus-old-goa.jpeg' => [
			'title'       => 'Basilica of Bom Jesus — Old Goa',
			'alt'         => 'UNESCO church facade Basilica Bom Jesus',
			'description' => 'UNESCO heritage for cultural tourism pages.',
			'caption'     => 'Baroque masterpiece at Old Goa.',
		],
		'nearby-anjuna-flea-market-goa.jpeg' => [
			'title'       => 'Anjuna flea market — Goa',
			'alt'         => 'Open-air market stalls and shoppers Anjuna',
			'description' => 'Weekend shopping ritual for North Goa stays.',
			'caption'     => 'Browse Anjuna’s famous Wednesday flea market.',
		],
	];
	return $map;
}

/**
 * @param string $filename Basename under assets/sample-media.
 * @return array{title: string, alt: string, description: string, caption: string}
 */
function pw_sample_default_seo_for_unknown_demo_media( $filename ) {
	$base = pathinfo( (string) $filename, PATHINFO_FILENAME );
	$base = str_replace( [ '-', '_' ], ' ', $base );
	$base = preg_replace( '/\s+/', ' ', trim( $base ) );
	$nice = $base !== '' ? ucwords( $base ) : 'Hotel image';
	return [
		'title'       => $nice,
		'alt'         => $nice,
		'description' => sprintf(
			'Photograph for the Portico Webworks hotel demo: %s. Title and alt describe the scene for accessibility and search.',
			strtolower( $nice )
		),
		'caption'     => $nice,
	];
}

/**
 * @param int    $attachment_id Attachment post ID.
 * @param string $filename      Basename as stored on disk (map key).
 */
function pw_sample_apply_demo_media_attachment_seo( $attachment_id, $filename ) {
	$attachment_id = (int) $attachment_id;
	if ( $attachment_id <= 0 || $filename === '' ) {
		return;
	}
	$map  = pw_sample_get_sample_media_seo_map();
	$data = isset( $map[ $filename ] ) ? $map[ $filename ] : pw_sample_default_seo_for_unknown_demo_media( $filename );
	wp_update_post(
		[
			'ID'           => $attachment_id,
			'post_title'   => $data['title'],
			'post_content' => $data['description'],
			'post_excerpt' => $data['caption'],
		]
	);
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $data['alt'] );
}
