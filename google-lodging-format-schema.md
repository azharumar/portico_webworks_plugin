# Google Lodging Format Schema

## Lodging

| Type | Field | Definition |
|---|---|---|
| [Metadata](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#metadata) | metadata | REQUIRED. The last updated timestamp must be specified. |
| string | place_id | REQUIRED. Unique identifier for the property. Either the [Place ID](https://developers.google.com/places/place-id) which uniquely identifies a place in the Google Places database and on Google Maps, or, the listing Hotel ID from your hotel list feed. |
| [Property](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#property) | property | General factual information about the property's physical structure and important dates. |
| [Services](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#services) | services | Conveniences or help provided by the property to facilitate an easier, more comfortable stay. |
| [Policies](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#policies) | policies | Property rules that impact guests. |
| [FoodAndDrink](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#foodanddrink) | food_and_drink | Meals, snacks, and beverages available at the property. |
| [Pools](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#pools) | pools | A body of water contained within a man-made structure either indoors or outdoors for the purpose of swimming, soaking, or recreation. |
| [Wellness](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#wellness) | wellness | Guest facilities at the property to promote or maintain health, beauty, and fitness. |
| [PublicBath](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#publicbath) | public_bath | Facilities related to public baths. |
| [Activities](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#activities) | activities | Amenities and features related to leisure and play. |
| [Transportation](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#transportation) | transportation | Vehicles or vehicular services facilitated or owned by the property. |
| [Families](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#families) | families | Groups of people who are related to each other, often consisting of adults and at least 1 child. |
| [Connectivity](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#connectivity) | connectivity | The ways in which the property provides guests with the ability to access the internet. |
| [Business](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#business) | business | Features of the property of specific interest to the business traveler. |
| [Accessibility](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#accessibility) | accessibility | Physical adaptations made to the property in consideration of varying levels of human physical ability. |
| [Pets](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#pets) | pets | Policies regarding guest-owned animals. |
| [Parking](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#parking) | parking | An area on-site at a property where one can temporarily leave a motor vehicle when not in use, or the services related to the act of parking in this area. |
| [Housekeeping](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#housekeeping) | housekeeping | Conveniences provided in guest units to facilitate an easier, more comfortable stay. |
| [HealthAndSafety](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#healthandsafety) | health_and_safety | Health and safety measures implemented by the hotel during COVID-19. |
| [Sustainability](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#sustainability) | sustainability | Sustainability practices implemented at the hotel. |
| [LivingArea](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#livingarea) | common_living_area | Additional amenities available in shared areas. |
| [GuestUnitType](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#guestunittype) | all_units | In-unit amenities available in ALL guest unit types. |
| [GuestUnitType](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#guestunittype) | some_units | In-unit amenities available in SOME guest unit types. |
| [repeated GuestUnitType](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#guestunittype) | guest_units | In-unit amenities available in individual guest unit types. |

## Metadata

| Type | Field | Definition |
|---|---|---|
| string | last_updated | REQUIRED. The ISO 8601 datetime at which the Lodging data is asserted to be true in the real world. Examples: "2018-11-13T13:14:52-0800", "2018-11-13T13:14:52Z" |

## Property

| Type | Field | Definition |
|---|---|---|
| int32 | built_year | The year that construction of the property was completed. |
| int32 | last_renovated_year | The year when a renovation of the property was completed. Renovation may include all or any combination of the following: the units, the public spaces, the exterior, or the interior. |
| int32 | number_of_rooms | The total number of rooms and suites bookable by guests for an overnight stay. Does not include event space, public spaces, conference rooms, fitness rooms, business centers, spa, salon, restaurants/bars, or shops. |
| int32 | floors | The number of stories the building has from the ground floor to the top floor. |

## Services

| Type | Field | Definition |
|---|---|---|
| float | class_rating | A rating system for classifying hotels based on their services and facilities. Typically a system ranging from 1 to 5 stars, with the greater number of stars indicating more services/luxury. In some countries, hotel class is governed legally or by a known hotel association. |
| bool | front_desk | A counter or desk in the lobby or the immediate interior of the hotel where a member of the staff greets guests and processes the information related to their stay (including check-in and check-out). May or may not be manned and open 24/7. |
| bool | front_desk_24hrs | Front desk is staffed 24 hours a day. |
| bool | concierge | Hotel staff member(s) responsible for facilitating an easy, comfortable stay through making reservations for meals, sourcing theater tickets, arranging tours, finding a doctor, making recommendations, and answering questions. |
| bool | elevator | There is a passenger elevator that transports guests from one story to another. Also known as lift. |
| bool | baggage_storage | A provision for guests to leave their bags at the hotel when they arrive for their stay before the official check-in time. May or may not apply for guests who wish to leave their bags after check-out and before departing the locale. Also known as bag dropoff. |
| bool | laundry_full_service | Laundry and dry cleaning facilitated and handled by the hotel on behalf of the guest. Does not include the provision for guests to do their own laundry in on-site machines. |
| bool | laundry_self_service | On-site clothes washers and dryers accessible to guests for the purpose of washing and drying their own clothes. May or may not require payment to use the machines. |
| bool | social_hour | A reception with complimentary soft drinks, tea, coffee, wine and/or cocktails in the afternoon or evening. Can be hosted by hotel staff or guests may serve themselves. Also known as wine hour. The availability of coffee/tea in the lobby throughout the day does not constitute a social or wine hour. |
| bool | wake_up_calls | By direction of the guest, a hotel staff member will phone the guest unit at the requested hour. Also known as morning call. |
| bool | convenience_store | A shop at the hotel primarily selling snacks, drinks, non-prescription medicines, health and beauty aids, magazines and newspapers. |
| bool | gift_shop | An on-site store primarily selling souvenirs, mementos and other gift items. May or may not also sell sundries, magazines and newspapers, clothing, or snacks. |
| bool | currency_exchange | A staff member or automated machine tasked with the transaction of providing the native currency of the hotel's locale in exchange for the foreign currency provided by a guest. |
| [LanguagesSpoken](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#languagesspoken) | languages_spoken | At least one staff member speaks the given language. |

## LanguagesSpoken

| Type | Field | Definition |
|---|---|---|
| bool | arabic_spoken | At least one staff member speaks Arabic. |
| bool | cantonese_spoken | At least one staff member speaks Cantonese. |
| bool | dutch_spoken | At least one staff member speaks Dutch. |
| bool | english_spoken | At least one staff member speaks English. |
| bool | filipino_spoken | At least one staff member speaks Filipino. |
| bool | french_spoken | At least one staff member speaks French. |
| bool | german_spoken | At least one staff member speaks German. |
| bool | hindi_spoken | At least one staff member speaks Hindi. |
| bool | indonesian_spoken | At least one staff member speaks Indonesian. |
| bool | italian_spoken | At least one staff member speaks Italian. |
| bool | japanese_spoken | At least one staff member speaks Japanese. |
| bool | korean_spoken | At least one staff member speaks Korean. |
| bool | mandarin_spoken | At least one staff member speaks Mandarin. |
| bool | portuguese_spoken | At least one staff member speaks Portuguese. |
| bool | russian_spoken | At least one staff member speaks Russian. |
| bool | spanish_spoken | At least one staff member speaks Spanish. |
| bool | vietnamese_spoken | At least one staff member speaks Vietnamese. |

## Housekeeping

| Type | Field | Definition |
|---|---|---|
| bool | housekeeping_available | Guest units are cleaned by hotel staff during guest's stay. Schedule may vary from daily, weekly, or specific days of the week. |
| bool | housekeeping_daily | Guest units are cleaned by hotel staff daily during guest's stay. |
| bool | turndown_service | Hotel staff enters guest units to prepare the bed for sleep use. May or may not include some light housekeeping. May or may not include an evening snack or candy. Also known as evening service. |

## HealthAndSafety

| Type | Field | Definition |
|---|---|---|
| [EnhancedCleaning](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#enhancedcleaning) | enhanced_cleaning |   |
| [IncreasedFoodSafety](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#increasedfoodsafety) | increased_food_safety |   |
| [MinimizedContact](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#minimizedcontact) | minimized_contact |   |
| [PersonalProtection](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#personalprotection) | personal_protection |   |
| [PhysicalDistancing](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#physicaldistancing) | physical_distancing |   |

## EnhancedCleaning

| Type | Field | Definition |
|---|---|---|
| bool | common_areas_enhanced_cleaning | Enhanced cleaning of common areas. |
| bool | guest_rooms_enhanced_cleaning | Enhanced cleaning of guest rooms. |
| bool | commercial_grade_disinfectant_cleaning | Commercial-grade disinfectant used to clean the property. |
| bool | employees_trained_cleaning_procedures | Employees trained in COVID-19 cleaning procedures. |
| bool | employees_trained_hand_washing_protocols | Employees trained in thorough hand-washing. |
| bool | employees_wear_protective_equipment | Employees wear masks, face shields, and/or gloves. |

## IncreasedFoodSafety

| Type | Field | Definition |
|---|---|---|
| bool | food_preparation_and_serving_safe_handling | Additional safety measures during food prep and serving. |
| bool | food_areas_additional_sanitation | Additional sanitation in dining areas. |
| bool | individual_packaged_meals_available | Individually-packaged meals. |
| bool | disposable_flatware | Disposable flatware. |
| bool | single_use_food_menus | Single-use menus. |

## MinimizedContact

| Type | Field | Definition |
|---|---|---|
| bool | no_high_touch_items_common_areas | High-touch items, such as magazines, removed from common areas. |
| bool | no_high_touch_items_guest_rooms | High-touch items, such as decorative pillows, removed from guest rooms. |
| bool | digital_guest_room_keys | Keyless mobile entry to guest rooms. |
| bool | plastic_keycards_disinfected | Plastic key cards are disinfected or discarded. |
| bool | room_bookings_buffer | Buffer maintained between room bookings. |
| bool | housekeeping_scheduled_request_only | Housekeeping scheduled by request only. |
| bool | contactless_checkin_checkout | No-contact check-in and check-out. |

## PersonalProtection

| Type | Field | Definition |
|---|---|---|
| bool | common_areas_offer_sanitizing_items | Hand-sanitizer and/or sanitizing wipes in common areas. |
| bool | guest_room_hygiene_kits_available | In-room hygiene kits with masks, hand sanitizer, and/or antibacterial wipes. |
| bool | protective_equipment_available | Masks and/or gloves available for guests. |
| bool | face_mask_required | Masks required on the property. |

## PhysicalDistancing

| Type | Field | Definition |
|---|---|---|
| bool | physical_distancing_required | Physical distancing required. |
| bool | plexiglass_used | Safety dividers at front desk and other locations. |
| bool | shared_areas_limited_occupancy | Guest occupancy limited within shared facilities. |
| bool | wellness_areas_have_private_spaces | Private spaces designated in spa and wellness areas. |
| bool | common_areas_physical_distancing_arranged | Common areas arranged to maintain physical distancing. |

## Sustainability

| Type | Field | Definition |
|---|---|---|
| [EnergyEfficiency](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#energyefficiency) | energy_efficiency |   |
| [WaterConservation](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#waterconservation) | water_conservation |   |
| [WasteReduction](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#wastereduction) | waste_reduction |   |
| [SustainableSourcing](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#sustainablesourcing) | sustainable_sourcing |   |
| [SustainabilityCertifications](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#sustainabilitycertifications) | sustainability_certifications |   |

## EnergyEfficiency

| Type | Field | Definition |
|---|---|---|
| bool | energy_conservation_program | The property tracks corporate-level Scope 1 and 2 GHG emissions, and Scope 3 emissions if available. The property has a commitment to implement initiatives that reduce GHG emissions year over year. The property has shown an absolute reduction in emissions for at least 2 years. Emissions are either verfied by a third-party and/or published in external communications. |
| bool | independent_organization_audits_energy_use | The property conducts an energy audit at least every 5 years, the results of which are either verified by a third-party and/or published in external communications. An energy audit is a detailed assessment of the facility which provides recommendations to existing operations and procedures to improve energy efficiency, available incentives or rebates,and opportunities for improvements through renovations or upgrades. Examples of organizations that conduct credible third party audits include: Engie Impact, DNV GL (EU), Dexma, and local utility providers (they often provide energy and water audits). |
| bool | carbon_free_energy_sources | Property sources carbon-free electricity via at least one of the following methods: on-site clean energy generation, power purchase agreement(s) with clean energy generators, green power provided by electricity supplier, or purchases of Energy Attribute Certificates (such as Renewable Energy Certificates or Guarantees of Origin). |
| bool | energy_efficient_heating_and_cooling_systems | The property doesn't use chlorofluorocarbon (CFC)-based refrigerants in heating, ventilating, and air-conditioning systems unless a third-party audit shows it's not economically feasible. The CFC-based refrigerants which are used should have a Global Warming Potential (GWP) ≤ 10. The property uses occupancy sensors on HVAC systems in back-of-house spaces, meeting rooms, and other low-traffic areas. |
| bool | energy_efficient_lighting | At least 75% of the property's lighting is energy efficient, using lighting that is more than 45 lumens per watt -- typically LED or CFL lightbulbs. |
| bool | energy_saving_thermostats | The property installed energy-saving thermostats throughout the building to conserve energy when rooms or areas are not in use. Energy-saving thermostats are devices that control heating/cooling in the building by learning temperature preferences and automatically adjusting to energy-saving temperatures as the default. The thermostats are automatically set to a temperature between 68-78 degrees F (20-26 °C), depending on seasonality. In the winter, set the thermostat to 68°F (20°C) when the room is occupied, lowering room temperature when unoccupied. In the summer, set the thermostat to 78°F (26°C) when the room is occupied. |

## WaterConservation

| Type | Field | Definition |
|---|---|---|
| bool | independent_organization_audits_water_use | The property conducts a water conservation audit every 5 years, the results of which are either verified by a third-party and/or published in external communications. A water conservation audit is a detailed assessment of the facility, providing recommendations to existing operations and procedures to improve water efficiency, available incentives or rebates, and opportunities for improvements through renovations or upgrades. Examples of organizations who conduct credible third party audits include: Engie Impact, and local utility providers (they often provide energy and water audits). |
| bool | water_saving_sinks | All of the property's guest rooms have bathroom faucets that use a maximum of 1.5 gallons per minute (gpm), public restroom faucets do not exceed 0.5 gpm, and kitchen faucets (excluding faucets used exclusively for filling operations) do not exceed 2.2 gpm. |
| bool | water_saving_toilets | All of the property's toilets use 1.6 gallons per flush, or less. |
| bool | water_saving_showers | All of the property's guest rooms have shower heads that use no more than 2.0 gallons per minute (gpm). |
| bool | towel_reuse_program | The property offers a towel reuse program. |
| bool | linen_reuse_program | The property offers a linen reuse program. |

## WasteReduction

| Type | Field | Definition |
|---|---|---|
| bool | recycling_program | The property has a recycling program, aligned with LEED waste requirements, and a policy outlining efforts to send less than 50% of waste to landfill. The recycling program includes storage locations for recyclable materials, including mixed paper, corrugated cardboard, glass, plastics, and metals. |
| bool | food_waste_reduction_program | The property has established a food waste reduction and donation program, aiming to reduce food waste by half. These programs typically use tools such as the Hotel Kitchen Toolkit and others to track waste and measure progress. |
| bool | donates_excess_food | The property has a program and/or policy for diverting waste from landfill that may include efforts to donate for human consumption or divert food for animal feed. |
| bool | composts_excess_food | The property has a program and/or policy for diverting waste from landfill by composting food and yard waste, either through compost collection and off-site processing or on-site compost processing. |
| bool | soap_donation_program | The property participates in a soap donation program such as Clean the World or something similar. |
| bool | toiletry_donation_program | The property participates in a toiletry donation program such as Clean the World or something similar. |
| bool | safely_handles_hazardous_substances | The property has a hazardous waste management program aligned wit GreenSeal and LEED requirements, and meets all regulatory requirements for hazardous waste disposal and recycling. Hazardous means substances that are classified as "hazardous" by an authoritative body (such as OSHA or DOT), are labeled with signal words such as "Danger," "Caution," "Warning," or are flammable, corrosive, or ignitable. Requirements include: - The property shall maintain records of the efforts it has made to replace the hazardous substances it uses with less hazardous alternatives. - An inventory of the hazardous materials stored on-site. - Products intended for cleaning, dishwashing, laundry, and pool maintenance shall be stored in clearly labeled containers. These containers shall be checked regularly for leaks, and replaced a necessary. - Spill containment devices shall be installed to collect spills, drips, or leaching of chemicals. <br /> |
| bool | safely_disposes_electronics | The property has a reputable recycling program that keeps hazardous electronic parts and chemical compounds out of landfills, dumps and other unauthorized abandonment sites, and recycles/reuses applicable materials. (e.g. certified electronics recyclers). |
| bool | safely_disposes_batteries | The property safely stores and disposes batteries. |
| bool | safely_disposes_lightbulbs | The property safely stores and disposes lightbulbs. |
| bool | refillable_toiletry_containers | The property has replaced miniature individual containers with refillable amenity dispensers for shampoo, conditioner, soap, and lotion. |
| bool | water_bottle_filling_stations | The property offers water stations throughout the building for guest use. |
| bool | compostable_food_containers_and_cutlery | 100% of food service containers and to-go cutlery are compostable, and reusable utensils are offered wherever possible. Compostable materials are capable of undergoing biological decomposition in a compost site, such that material is not visually distinguishable and breaks down into carbon dioxide, water, inorganic compounds, and biomass. |
| bool | no_styrofoam_food_containers | The property eliminates the use of Styrofoam in disposable food service items. |
| bool | no_single_use_plastic_water_bottles | The property bans single-use plastic water bottles. |
| bool | no_single_use_plastic_straws | The property bans single-use plastic straws. |

## SustainableSourcing

| Type | Field | Definition |
|---|---|---|
| bool | responsible_purchasing_policy | The property has a responsible procurement policy in place. Responsible means integration of social, ethical, and/or environmental performance factors into the procurement process when selecting suppliers. |
| bool | organic_food_and_beverages | At least 25% of food and beverages, by spend, are certified organic. Organic means products that are certified to one of the organic standard listed in the IFOAM family of standards. Qualifying certifications include USDA Organic and EU Organic, among others. |
| bool | locally_sourced_food_and_beverages | Property sources locally in order to lower the environmental footprint from reduced transportation and to stimulate the local economy. Products produced less than 62 miles from the establishment are normally considered as locally produced. |
| bool | responsibly_sources_seafood | The property does not source seafood from the Monterey Bay Aquarium Seafood Watch "avoid" list, and must sustainably source seafood listed as "good alternative," "eco-certified," and "best choice". The property has a policy outlining a commitment to source Marine Stewardship Council (MSC) and/or Aquaculture Stewardship Council (ASC) Chain of Custody certified seafood. |
| bool | organic_cage_free_eggs | The property sources 100% certified organic and cage-free eggs (shell, liquid, and egg products). Cage-free means hens are able to walk, spread their wings and lay their eggs in nests). |
| bool | vegetarian_meals | The property provides vegetarian menu options for guests. Vegetarian food does not contain meat, poultry, fish, or seafood. |
| bool | vegan_meals | The property provides vegan menu options for guests. Vegan food does not contain animal products or byproducts. |
| bool | eco_friendly_toiletries | Soap, shampoo, lotion, and other toiletries provided for guests have a nationally or internationally recognized sustainability certification, such as USDA Organic, EU Organic, or cruelty-free. |

## SustainabilityCertifications

| Type | Field | Definition |
|---|---|---|
| bool | abcd_tourism_lanotetouristique | ABCD Tourism - LaNoteTouristique. |
| bool | actively_green_bronze | Actively Green: Bronze. |
| bool | actively_green_silver | Actively Green: Silver. |
| bool | actively_green_gold | Actively Green: Gold. |
| bool | arc360_ambassador | ARC360: Ambassador. |
| bool | asian_ecotourism | Asian Ecotourism Standard for Accommodations (AESA). |
| bool | audubon_international | Audubon International. |
| bool | austrian_ecolabel | Austrian Ecolabel. |
| bool | b_lab_global | B Lab Global. |
| bool | beyond_green | Beyond Green. |
| bool | bio_hotels | Bio Hotels. |
| bool | biolia | Biolia. |
| bool | bioscore_sustainable_c | Bioscore Sustainable: C. |
| bool | bioscore_sustainable_b | Bioscore Sustainable: B. |
| bool | bioscore_sustainable_a | Bioscore Sustainable: A. |
| bool | bioscore_sustainable_a_plus | Bioscore Sustainable: A+. |
| bool | biosphere_responsible_tourism | Biosphere Responsible Tourism Standard. |
| bool | breeam_pass | BREEAM: Pass. |
| bool | breeam_good | BREEAM: Good. |
| bool | breeam_very_good | BREEAM: Very Good. |
| bool | breeam_excellent | BREEAM: Excellent. |
| bool | breeam_outstanding | BREEAM: Outstanding. |
| bool | cenia_efs | CENIA EFS (Environmentally Friendly Service). |
| bool | certified_green_hotel_good | Certified Green Hotel: Good. |
| bool | certified_green_hotel_very_good | Certified Green Hotel: Very Good. |
| bool | certified_green_hotel_excellent | Certified Green Hotel: Excellent. |
| bool | china_hospitality_association | China Hospitality Association. |
| bool | climate_partner | ClimatePartner. |
| bool | costa_rica_sustainable_tourism_basico | Costa Rica Certification for Sustainable Tourism (CST): Basico. |
| bool | costa_rica_sustainable_tourism_elite | Costa Rica Certification for Sustainable Tourism (CST): Elite. |
| bool | dca_esg_sustainable | DCA ESG: Sustainable. |
| bool | dca_esg_sustainable_l | DCA ESG: Sustainable: L. |
| bool | dehoga_umweltcheck_bronze | DEHOGA Umweltcheck: Bronze. |
| bool | dehoga_umweltcheck_silver | DEHOGA Umweltcheck: Silver. |
| bool | dehoga_umweltcheck_gold | DEHOGA Umweltcheck: Gold. |
| bool | earthcheck_silver | EarthCheck: Silver. |
| bool | earthcheck_gold | EarthCheck: Gold. |
| bool | earthcheck_platinum | EarthCheck: Platinum. |
| bool | earthcheck_master | EarthCheck: Master. |
| bool | ecosmart | ECOSmart. |
| bool | eco_certification_malta | Eco-Certification Malta Standard. |
| bool | eco_climate_badge_bronze | Eco Climate Badge: Bronze. |
| bool | eco_climate_badge_silver | Eco Climate Badge: Silver. |
| bool | eco_climate_badge_gold | Eco Climate Badge: Gold. |
| bool | eco_romania | Eco-Romania. |
| bool | ecostars_level1 | Ecostars: 1 Ecostar. |
| bool | ecostars_level2 | Ecostars: 2 Ecostars. |
| bool | ecostars_level3 | Ecostars: 3 Ecostars. |
| bool | ecostars_level4 | Ecostars: 4 Ecostars. |
| bool | ecostars_level5 | Ecostars: 5 Ecostars. |
| bool | ecotourism_australia_ecotourism | Ecotourism Australia ECO Certification Standard: Ecotourism. |
| bool | ecotourism_australia_ecotourism_advanced | Ecotourism Australia ECO Certification Standard: EcotourismAdvanced. |
| bool | ecotourism_kenya_eco_rating_bronze | Ecotourism Kenya Eco-rating Certification Scheme: Bronze. |
| bool | ecotourism_kenya_eco_rating_silver | Ecotourism Kenya Eco-rating Certification Scheme: Silver. |
| bool | ecotourism_kenya_eco_rating_gold | Ecotourism Kenya Eco-rating Certification Scheme: Gold. |
| bool | ecoworldhotel_level1 | Ecoworldhotel: 1 Eco-Leaf. |
| bool | ecoworldhotel_level2 | Ecoworldhotel: 2 Eco-Leaves. |
| bool | ecoworldhotel_level3 | Ecoworldhotel: 3 Eco-Leaves. |
| bool | ecoworldhotel_level4 | Ecoworldhotel: 4 Eco-Leaves. |
| bool | ecoworldhotel_level5 | Ecoworldhotel: 5 Eco-Leaves. |
| bool | edge_green_building | EDGE Green Building Certification. |
| bool | emas | Eco-Management and Audit Scheme (EMAS). |
| bool | eu_ecolabel | EU Ecolabel. |
| bool | fairmoove_environmental_footprint_a | FairMoove Environmental Footprint: A. |
| bool | fairmoove_environmental_footprint_b | FairMoove Environmental Footprint: B. |
| bool | fairmoove_environmental_footprint_c | FairMoove Environmental Footprint: C. |
| bool | fairmoove_environmental_footprint_d | FairMoove Environmental Footprint: D. |
| bool | fairmoove_environmental_footprint_e | FairMoove Environmental Footprint: E. |
| bool | fair_trade_tourism | Fair Trade Tourism. |
| bool | fifty_shades_greener_bronze | Fifty Shades Greener: Bronze. |
| bool | fifty_shades_greener_silver | Fifty Shades Greener: Silver. |
| bool | fifty_shades_greener_gold | Fifty Shades Greener: Gold. |
| bool | fifty_shades_greener_emerald | Fifty Shades Greener: Emerald. |
| bool | fondation_les_pages_vertes | Fondation Les Pages vertes. |
| bool | futureplus | FuturePlus |
| bool | global_ecosphere_retreats_standard | Global Ecosphere Retreats Standard. |
| bool | great_green_deal | GREAT Green Deal Certification. |
| bool | green_destinations_level1 | Green Destinations: Level 1. |
| bool | green_destinations_level2 | Green Destinations: Level 2. |
| bool | green_destinations_level3 | Green Destinations: Level 3. |
| bool | green_globe_certified | Green Globe: Certified. |
| bool | green_globe_gold | Green Globe: Gold. |
| bool | green_globe_platinum | Green Globe: Platinum. |
| bool | green_growth2050_silver | Green Growth 2050 Standard: Silver. |
| bool | green_growth2050_gold | Green Growth 2050 Standard: Gold. |
| bool | green_growth2050_platinum | Green Growth 2050 Standard: Platinum. |
| bool | green_hospitality | Green Hospitality Certified. |
| bool | green_key | Green Key. |
| bool | green_key_global_level1 | Green Key Global Eco-Rating: 1 Green Key. |
| bool | green_key_global_level2 | Green Key Global Eco-Rating: 2 Green Keys. |
| bool | green_key_global_level3 | Green Key Global Eco-Rating: 3 Green Keys. |
| bool | green_key_global_level4 | Green Key Global Eco-Rating: 4 Green Keys. |
| bool | green_key_global_level5 | Green Key Global Eco-Rating: 5 Green Keys. |
| bool | green_leaf_foundation | Green Leaf Foundation. |
| bool | green_pearls_unique_places | Green Pearls Unique Places. |
| bool | green_real_estate_greenre | Green Real Estate (GreenRE). |
| bool | green_seal_bronze | Green Seal: Bronze. |
| bool | green_seal_silver | Green Seal: Silver. |
| bool | green_seal_gold | Green Seal: Gold. |
| bool | green_sign | GreenSign Hotel |
| bool | green_star_level3 | Green Star Hotel Standard: 3 Stars. |
| bool | green_star_level4 | Green Star Hotel Standard: 4 Stars. |
| bool | green_star_level5 | Green Star Hotel Standard: 5 Stars. |
| bool | green_step_sustainable_tourism_bronze | GreenStep Sustainable Tourism: Bronze. |
| bool | green_step_sustainable_tourism_silver | GreenStep Sustainable Tourism: Silver. |
| bool | green_step_sustainable_tourism_gold | GreenStep Sustainable Tourism: Gold. |
| bool | green_step_sustainable_tourism_platinum | GreenStep Sustainable Tourism: Platinum. |
| bool | green_tourism_bronze | Green Tourism: Bronze. |
| bool | green_tourism_silver | Green Tourism: Silver. |
| bool | green_tourism_gold | Green Tourism: Gold. |
| bool | green_tourism_active_green_initiate | Green Tourism Active: Green Initiate. |
| bool | green_tourism_active_green_leader | Green Tourism Active: Green Leader. |
| bool | green_tourism_active_green_champion | Green Tourism Active: Green Champion. |
| bool | green_tourism_active_green_champion_distinction | Green Tourism Active: Green Champion with Distinction. |
| bool | gstc_criteria | GSTC Criteria. |
| bool | hostelling_international_quality_and_sustainability_small | Hostelling International Quality and Sustainability Standard: Small. |
| bool | hostelling_international_quality_and_sustainability_standard | Hostelling International Quality and Sustainability Standard: Standard. |
| bool | hostelling_international_quality_and_sustainability_key | Hostelling International Quality and Sustainability Standard: Key. |
| bool | hotel_sustainability_basics | Hotel Sustainability Basics. |
| bool | hoteles_mas_verdes_bronce | Hoteles más Verdes: Bronce. |
| bool | hoteles_mas_verdes_plata | Hoteles más Verdes: Plata. |
| bool | hoteles_mas_verdes_oro | Hoteles más Verdes: Oro. |
| bool | ibex_fairstay_bronze | ibex fairstay: Bronze. |
| bool | ibex_fairstay_silver | ibex fairstay: Silver. |
| bool | ibex_fairstay_gold | ibex fairstay: Gold. |
| bool | ibex_fairstay_platinum | ibex fairstay: Platinum. |
| bool | intertek_ecocheck_standard | Intertek Ecocheck Standard. |
| bool | iso14001 | ISO 14001. |
| bool | iso50001 | ISO 50001. |
| bool | iso9001 | ISO 9001. |
| bool | jea_eco_mark_programme | JEA Eco Mark Programme. |
| bool | leed_certified | LEED: Certified. |
| bool | leed_silver | LEED: Silver. |
| bool | leed_gold | LEED: Gold. |
| bool | leed_platinum | LEED: Platinum. |
| bool | miosotis_azores_standard | Miosotis Azores Standard. |
| bool | mission_zero_academy_miza_level1 | Mission Zero Academy (MiZA) : Level 1. |
| bool | mission_zero_academy_miza_level2 | Mission Zero Academy (MiZA) : Level 2. |
| bool | mission_zero_academy_miza_level3 | Mission Zero Academy (MiZA) : Level 3. |
| bool | nabers_energy | NABERS Energy. |
| bool | nabers_water | NABERS Water. |
| bool | nordic_swan_ecolabel | Nordic Swan Ecolabel. |
| bool | preferred_by_nature_sustainable_tourism | Preferred by Nature Sustainable Tourism Standard for Accommodation. |
| bool | qia_services_green | QIA Services: Green. |
| bool | qia_services_silver | QIA Services: Silver. |
| bool | qia_services_gold | QIA Services: Gold. |
| bool | qualmark_bronze | Qualmark: Bronze. |
| bool | qualmark_silver | Qualmark: Silver. |
| bool | qualmark_gold | Qualmark: Gold. |
| bool | queervadis | Queervadis. |
| bool | sakura_quality_level1 | Sakura Quality An ESG Practice Standard: 1 Cherry Blossom. |
| bool | sakura_quality_level2 | Sakura Quality An ESG Practice Standard: 2 Cherry Blossoms. |
| bool | sakura_quality_level3 | Sakura Quality An ESG Practice Standard: 3 Cherry Blossoms. |
| bool | sakura_quality_level4 | Sakura Quality An ESG Practice Standard: 4 Cherry Blossoms. |
| bool | sakura_quality_level5 | Sakura Quality An ESG Practice Standard: 5 Cherry Blossoms. |
| bool | sernatur_sello_level1 | SERNATUR Sello S: Level 1. |
| bool | sernatur_sello_level2 | SERNATUR Sello S: Level 2. |
| bool | sernatur_sello_level3 | SERNATUR Sello S: Level 3. |
| bool | seychelles_sustainable_tourism_label | Seychelles Sustainable Tourism Label. |
| bool | smartcertificationit | SMARTCERTIFICATION.IT. |
| bool | socotec_sums | SOCOTEC SuMS. |
| bool | sustainable_meetings_berlin | Sustainable Meetings Berlin. |
| bool | sustainable_tourism_network | Sustainable Tourism Network Certification. |
| bool | sustainable_travel_ireland_bronze | Sustainable Travel Ireland -- GSTC Industry Criteria: Bronze. |
| bool | sustainable_travel_ireland_silver | Sustainable Travel Ireland -- GSTC Industry Criteria: Silver. |
| bool | sustainable_travel_ireland_gold | Sustainable Travel Ireland -- GSTC Industry Criteria: Gold. |
| bool | sustonica_sustainable_vacation_rental | Sustonica - Sustainable Vacation Rental. |
| bool | tof_tigers_footprint_good | TOFTigers Footprint Certification: Good. |
| bool | tof_tigers_footprint_quality | TOFTigers Footprint Certification: Quality. |
| bool | tof_tigers_footprint_outstanding | TOFTigers Footprint Certification: Outstanding. |
| bool | tof_tigers_pug_good | TOFTigers PUG certification: Good. |
| bool | tof_tigers_pug_quality | TOFTigers PUG certification: Quality. |
| bool | tof_tigers_pug_outstanding | TOFTigers PUG certification: Outstanding. |
| bool | tourcert_certification | TourCert Certification. |
| bool | travelife | Travelife Standard for Hotels \& Accommodations. |
| bool | tudestino_sostenible | Tudestino Sostenible. |
| bool | turkiye_sustainable_tourism_program | Türkiye Sustainable Tourism Program. |
| bool | viabono | Viabono. |

## Parking

| Type | Field | Definition |
|---|---|---|
| bool | parking_available | The hotel allows the cars of guests to be parked, either for free or for a fee. Parking facility may be an outdoor lot or an indoor garage, but must be onsite. Nearby parking does not apply. Parking may be performed by the guest or by hotel staff. |
| bool | parking_for_free | The hotel allows the cars of guests to be parked without a fee. Parking facility may be an outdoor lot or an indoor garage, but must be onsite. Nearby parking does not apply. Parking may be performed by the guest or by hotel staff. Free parking must be available to all guests (limited conditions does not apply). |
| bool | self_parking_available | Guests park their own cars. Parking facility may be an outdoor lot or an indoor garage, but must be onsite. Nearby parking does not apply. Can be paid or free of charge. |
| bool | self_parking_for_free | Guests park their own cars for free. Parking facility may be an outdoor lot or an indoor garage, but must be onsite. Nearby parking does not apply. |
| bool | valet_parking_available | Hotel staff member parks the cars of guests. Parking with this service can be paid or free of charge. |
| bool | valet_parking_for_free | Hotel staff member parks the cars of guests. Parking with this service is free. |
| bool | electric_car_charging_stations | Electric power stations, usually located outdoors, into which guests plug their electric cars to receive a charge. |

## Policies

| Type | Field | Definition |
|---|---|---|
| string | check_in_time | The hour of the day at which the hotel begins providing guests access to their unit at the beginning of their stay. |
| string | check_out_time | The hour of the day on the last day of a guest's reserved stay at which the guest must vacate their room and settle their bill. Some hotels may offer late or early check out for a fee. |
| bool | kids_stay_for_free | The children of guests are allowed to stay in the room/suite of a parent or adult without an additional fee. The policy may or may not stipulate a limit of the child's age or the overall number of children allowed. |
| int32 | max_number_of_kids_stay_for_free | The hotel allows a specific, defined number of children to stay in the room/suite of a parent or adult without an additional fee. |
| int32 | max_child_age | The hotel allows children up to a certain age to stay in the room/suite of a parent or adult without an additional fee. |
| bool | smoke_free_property | Smoking is not allowed inside the building, on balconies, or in outside spaces. Hotels that offer a designated area for guests to smoke are not considered smoke-free properties. |
| bool | all_inclusive_available | The hotel offers a rate option that includes the cost of the room, meals, activities, and other amenities that might otherwise be charged separately. |
| bool | all_inclusive_only | The only rate option offered by the hotel is a rate that includes the cost of the room, meals, activities and other amenities that might otherwise be charged separately. |
| [PaymentOptions](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#paymentoptions) | payment_options | Forms of payment accepted. |

## PaymentOptions

| Type | Field | Definition |
|---|---|---|
| bool | cash | The hotel accepts payment by paper/coin currency. |
| bool | cheque | The hotel accepts a printed document issued by the guest's bank in the guest's name as a form of payment. |
| bool | credit_card | The hotel accepts payment by a card issued by a bank or credit card company. Also known as charge card, debit card, bank card, or charge plate. |
| bool | debit_card | The hotel accepts a bank-issued card that immediately deducts the charged funds from the guest's bank account upon processing. |
| bool | mobile_nfc | The hotel has the compatible computer hardware terminal that reads and charges a payment app on the guest's smartphone without requiring the two devices to make physical contact. Also known as Apple Pay, Google Pay, Samsung Pay. |

## FoodAndDrink

| Type | Field | Definition |
|---|---|---|
| bool | room_service | A hotel staffer delivers meals prepared onsite to a guest's room as per their request. May or may not be available during specific hours. Services should be available to all guests (not based on rate/room booked/reward program, etc). |
| bool | room_service_24hrs | Room service is available 24 hours a day. |
| bool | restaurant | A business onsite at the hotel that is open to the public as well as guests, and offers meals and beverages to consume at tables or counters. May or may not include table service. Also known as cafe, buffet, eatery. A "breakfast room" where the hotel serves breakfast only to guests (not the general public) does not count as a restaurant. |
| int32 | number_of_restaurants | The number of businesses at the hotel that are open to the public as well as guests and offer meals and beverages to consume onsite at tables or counters. May or may not include table service. Also known as cafe, buffet, or eatery. |
| bool | table_service | A restaurant in which a staff member is assigned to a guest's table to take their order, deliver and clear away food, and deliver the bill, if applicable. Also known as sit-down restaurant. |
| bool | buffet | A type of meal where guests serve themselves from a variety of dishes/foods that are put out on a table. |
| bool | buffet_dinner | Dinner meal service where guests serve themselves from a variety of dishes/foods that are put out on a table. |
| bool | buffet_breakfast | Breakfast meal service where guests serve themselves from a variety of dishes/foods that are put out on a table. |
| bool | breakfast_available | The morning meal is offered to all guests. May be free or for a charge. |
| bool | breakfast_for_free | Breakfast is offered free to all guests. Does not apply if only limited to certain room packages. |
| bool | bar | A designated room, lounge or area of a restaurant with seating at a counter behind which a hotel staffer takes the guest's order and provides the requested alcoholic drink. May be indoors or outdoors. Also known as Pub. |
| bool | vending_machine | A glass-fronted mechanized cabinet displaying and dispensing snacks and beverages for purchase by coins, paper money and/or credit cards. |

## Pools

| Type | Field | Definition |
|---|---|---|
| bool | pool | The presence of a pool, either indoors or outdoors. |
| int32 | number_of_pools | The sum of all pools at the hotel. |
| bool | indoor_pool | A pool located inside the hotel and available for guests to use for swimming and/or soaking. Use may or may not be restricted to adults and/or children. |
| int32 | number_of_indoor_pools | The sum of all indoor pools at the hotel. |
| bool | outdoor_pool | A pool located outside on the grounds of the hotel and available for guests to use for swimming, soaking or recreation. Use may or may not be restricted to adults and/or children. |
| int32 | number_of_outdoor_pools | The sum of all outdoor pools at the hotel. |
| bool | hot_tub | A man-made pool containing bubbling water maintained at a higher temperature and circulated by aerating jets for the purpose of soaking, relaxation and hydrotherapy. Can be indoors or outdoors. Not used for active swimming. Also known as Jacuzzi. Hot tub must be in a common area where all guests can access it. Does not apply to room-specific hot tubs that are only accessible to guest occupying that room. |
| bool | waterslide | A continuously wetted chute positioned by an indoor or outdoor pool which people slide down into the water. |
| bool | lazy_river | A man-made pool or several interconnected recreational pools built to mimic the shape and current of a winding river where guests float in the water on inflated rubber tubes. Can be indoors or outdoors. |
| bool | adult_pool | A pool inside the hotel or outside on hotel grounds restricted for use by adults only. |
| bool | wading_pool | A shallow pool designed for small children to play in. May be indoors or outdoors. Also known as kiddie pool. |
| bool | wave_pool | A large indoor or outdoor pool with a machine that produces water currents to mimic the ocean's crests. |
| bool | thermal_pool | A naturally occurring outdoor body of water that is heated by the earth's crust to a higher temperature, or an indoor or outdoor man-made pool that contains water maintained at a temperature higher than commonly found in standard swimming pools. The pools are used for soaking, relaxation and hydrotherapy. Not used for active swimming. Also known as mineral spa or mineral spring. |
| bool | water_park | An aquatic recreation area with a large pool or series of pools that has features such as a water slide or tube, wavepool, fountains, rope swings, and/or obstacle course. May be indoors or outdoors. Also known as adventure pool. |
| bool | lifeguard | A trained member of the hotel staff stationed by the hotel's indoor or outdoor swimming area and responsible for the safety of swimming guests. |

## Wellness

| Type | Field | Definition |
|---|---|---|
| bool | fitness_center | A room or building at the hotel containing equipment to promote physical activity, such as treadmills, elliptical machines, stationary bikes, weight machines, free weights, and/or stretching mats. Use of the fitness center may or may not be free of charge. May or may not be staffed. May or may not offer instructor-led classes in various styles of physical conditioning. May or may not be open 24/7. May or may not include locker rooms and showers. Also known as health club, gym, fitness room, health center. |
| bool | fitness_center_for_free | Guests may use the fitness center for free. |
| bool | elliptical_machine | An electric, stationary fitness machine with pedals that simulates climbing, walking or running and provides a user-controlled range of speeds and tensions. May not have arm-controlled levers to work out the upper body as well. Commonly found in a gym, fitness room, health center, or health club. |
| bool | treadmill | An electric stationary fitness machine that simulates a moving path to promote walking or running within a range of user-controlled speeds and inclines. Also known as running machine. Commonly found in a gym, fitness room, health center, or health club. |
| bool | weight_machine | Non-electronic fitness equipment designed for the user to target the exertion of different muscles. Usually incorporates a padded seat, a stack of flat weights and various bars and pulleys. May be designed for toning a specific part of the body or may involve different user-controlled settings, hardware and pulleys so as to provide an overall workout in one machine. Commonly found in a gym, fitness center, fitness room, or health club. |
| bool | free_weights | Individual handheld fitness equipment of varied weights used for upper body strength training or bodybuilding. Also known as barbells, dumbbells, or kettlebells. Often stored on a rack with the weights arranged from light to heavy. Commonly found in a gym, fitness room, health center, or health club. |
| bool | spa | A designated area, room or building at the hotel offering health and beauty treatment through such means as steam baths, exercise equipment, and massage. May also offer facials, nail care, and hair care. Services are usually available by appointment and for an additional fee. Does not apply if hotel only offers a steam room; must offer other beauty and/or health treatments as well. |
| bool | salon | A room at the hotel where professionals provide hair styling services such as shampooing, blow drying, hair dos, hair cutting and hair coloring. Also known as hairdresser or beauty salon. |
| bool | sauna | A wood-paneled room heated to a high temperature where guests sit on built-in wood benches for the purpose of perspiring and relaxing their muscles. Can be dry or slightly wet heat. Not a steam room. |
| bool | massage | A service provided by a trained massage therapist involving the physical manipulation of a guest's muscles in order to achieve relaxation or pain relief. |
| bool | aesthetic_salon | The property has an aesthetic salon which is a business that typically provides beauty treatments and services such as nail care and makeup application in Japan. |
| bool | doctor_on_call | The hotel has a contract with a medical professional who provides services to hotel guests should they fall ill during their stay. The doctor may or may not have an on-site office or be at the hotel at all times. |

## Activities

| Type | Field | Definition |
|---|---|---|
| bool | game_room | There is a room at the hotel containing electronic machines for play such as pinball, prize machines, driving simulators, and other items commonly found at a family fun center or arcade. May also include non-electronic games like pool, foosball, darts, and more. May or may not be designed for children. Also known as arcade, fun room, or family fun center. |
| bool | nightclub | There is a room at the hotel with a bar, a dance floor, and seating where designated staffers play dance music. There may also be a designated area for the performance of live music, singing and comedy acts. |
| bool | casino | A space designated for gambling and gaming featuring croupier-run table and card games, as well as electronic slot machines. May be on hotel premises or located nearby. |
| bool | boutique_stores | There are stores selling clothing, jewelry, art and decor either on hotel premises or very close by. Does not refer to the hotel gift shop or convenience store. |
| bool | tennis | The hotel has the requisite court(s) on site or has an affiliation with a nearby facility for the purpose of providing guests with the opportunity to play a two-sided court-based game in which players use a stringed racquet to hit a ball across a net to the side of the opposing player. The court can be indoors or outdoors. Instructors, racquets and balls may or may not be provided. |
| bool | golf | There is a golf course on hotel grounds or there is a nearby, independently run golf course that allows use by hotel guests. May be free or for a charge. |
| bool | horseback_riding | The hotel has a horse barn onsite or an affiliation with a nearby barn to allow for guests to sit astride a horse and direct it to walk, trot, cantor, gallop and/or jump. Can be in a riding ring, on designated paths, or in the wilderness. May or may not involve instruction. |
| bool | snorkeling | The provision for guests to participate in a recreational water activity in which swimmers wear a diving mask, a simple, shaped breathing tube and flippers/swim fins for the purpose of exploring below the surface of an ocean, gulf or lake. Does not usually require user certification or professional supervision. Equipment may or may not be available for rent or purchase. Not scuba diving. |
| bool | scuba | The provision for guests to dive under naturally occurring water fitted with a self-contained underwater breathing apparatus (SCUBA) for the purpose of exploring underwater life. Apparatus consists of a tank providing oxygen to the diver through a mask. Requires certification of the diver and supervision. The hotel may have the activity at its own waterfront or have an affiliation with a nearby facility. Required equipment is most often supplied to guests. May or may not require a fee. Not snorkeling. Not done in a swimming pool. |
| bool | water_skiing | The provision of giving guests the opportunity to be pulled across naturally occurring water while standing on skis and holding a tow rope attached to a motorboat. Can occur on hotel premises or at a nearby waterfront. Most often performed in a lake or ocean. |
| bool | bicycles_rental | The hotel owns bicycles that it permits guests to borrow and use. Can be free of charge or for a fee. |
| bool | bicycles_rental_for_free | The hotel owns bicycles that it permits guests to borrow and use for free. |
| bool | watercraft_rental | The hotel owns water vessels that it permits guests to borrow and use. Can be free of charge or for a fee. Watercraft may include boats, pedal boats, rowboats, sailboats, powerboats, canoes, kayaks, or personal watercraft (such as a Jet Ski). |
| bool | watercraft_rental_for_free | The hotel owns water vessels that it permits guests to borrow and use for free. Watercraft may include boats, pedal boats, rowboats, sailboats, powerboats, canoes, kayaks, or personal watercraft (such as a Jet Ski). |
| bool | beach_access | Hotel property is in close proximity to a beach and offers a way to get to that beach. This can include a route to the beach such as stairs down if hotel is on a bluff, or a short trail. Not the same as beachfront (with beach access, the hotel's proximity is close to but not right on the beach). |
| bool | private_beach | The beach which is in close proximity to the hotel is open only to guests. |
| bool | beach_front | Lodging establishment is physically located on the beach alongside an ocean, sea, gulf, or bay. It is not on a lake, river, stream, or pond. The hotel is not separated from the beach by a public road allowing vehicular, pedestrian, or bicycle traffic. |
| bool | karaoke | The property has Karaoke room(s), a form of entertainment in which people sing along with recorded music using a microphone. Can be free of charge or for a fee. |
| bool | banquet_hall | The property has a banquet hall which is a large room in a hotel or other venue that is used for hosting events such as weddings, conferences, or other large gatherings. |
| bool | table_tennis | The property has a room or a space where people play table tennis. Also known as Ping Pong. |

## Transportation

| Type | Field | Definition |
|---|---|---|
| bool | transfer | Hotel provides a shuttle service or car service to take guests to and from the nearest airport or train station. May be free or for a charge. Guests may share the vehicle with other guests unknown to them. |
| bool | airport_shuttle | Hotel provides guests with a chauffeured van or bus to and from the airport. May be free or for a charge. Guests may share the vehicle with other guests unknown to them. Applies if the hotel has a third-party shuttle service (office/desk etc.) within the hotel. As long as hotel provides this service, it doesn't matter if it's directly with them or a third party they work with. Does not apply if guest has to coordinate with an entity outside/other than the hotel. |
| bool | airport_shuttle_for_free | Hotel provides guests with a chauffeured van or bus to and from the airport for free. Must be free to all guests without any conditions. Guests may share the vehicle with other guests unknown to them. |
| bool | local_shuttle | A car, van or bus provided by the hotel to transport guests to destinations within a specified range of distance around the hotel. Usually shopping and/or convention centers, downtown districts, or beaches. May be free or for a charge. |
| bool | car_rental_on_property | A branch of a rental car company with a processing desk in the hotel. Available cars for rent may be awaiting at the hotel or in a nearby lot. |
| bool | private_car_service | Hotel provides a private chauffeured car to transport guests to destinations. Passengers in the car are either alone or are known to one another and have requested the car together. Service may be free or for a charge and travel distance is usually limited to a specific range. Not a taxi. |
| bool | private_car_service_for_free | Private chauffeured car service is free to guests. |

## Families

| Type | Field | Definition |
|---|---|---|
| bool | babysitting | Child care that is offered by hotel staffers or coordinated by hotel staffers with local child care professionals. May be free or for a charge. |
| bool | kids_activities | Recreational options such as sports, films, crafts and games designed for the enjoyment of children and offered at the hotel. May or may not be supervised. May or may not be at a designated time or place. May be free or for a charge. |
| bool | kids_club | An organized program of group activities held at the hotel and designed for the enjoyment of children. Facilitated by hotel staff (or staff procured by the hotel) in an area(s) designated for the purpose of entertaining children without their parents. May include games, outings, water sports, team sports, arts and crafts, and films. Usually has set hours. May be free or for a charge. Also known as Kids Camp or Kids program. |
| bool | kids_friendly | The hotel has one or more special features for families with children, such as reduced rates, child-sized beds, kids' club, babysitting service, or suitable place to play on premises. |

## Connectivity

| Type | Field | Definition |
|---|---|---|
| bool | wifi_available | The hotel provides the ability for guests to wirelessly connect to the internet. Can be in the public areas of the hotel and/or in the guest rooms. May be free or paid. |
| bool | wifi_for_free | The hotel offers guests the ability to wirelessly access the internet free of charge. |
| bool | wifi_in_public_areas | Guests have the ability to wirelessly connect to the internet in the areas of the hotel accessible to anyone. May be free or paid. |
| bool | public_internet_terminal | An area of the hotel supplied with computers and designated for the purpose of providing guests with the ability to access the internet. |

## Business

| Type | Field | Definition |
|---|---|---|
| bool | business_center | A designated room at the hotel with one or more desks and equipped with guest-use computers, printers, fax machines and/or photocopiers. May or may not be open 24/7. May or may not require a key to access. Not a meeting room or conference room. |
| bool | meeting_rooms | Rooms at the hotel designated for business-related gatherings. Rooms are usually equipped with tables or desks, office chairs and audio/visual facilities to allow for presentations and conference calls. Also known as conference rooms. |
| int32 | number_of_meeting_rooms | The number of rooms at the property designated for business meetings. Rooms are usually equipped with tables or desks, office chairs and audio/visual facilities to allow for presentations and conference calls. |

## Accessibility

| Type | Field | Definition |
|---|---|---|
| bool | mobility_accessible | Throughout the property there are physical adaptations to ease the stay of a person in a wheelchair, such as auto-opening doors, wide elevators, wide bathrooms or ramps. |
| bool | mobility_accessible_parking | The presence of a marked, designated area of prescribed size in which only registered, labeled vehicles transporting a person with physical challenges may park. |
| bool | mobility_accessible_elevator | A lift that transports people from one level to another and is built to accommodate a wheelchair-using passenger owing to the width of its doors and placement of call buttons. |
| bool | mobility_accessible_pool | A swimming pool equipped with a mechanical chair that can be lowered and raised for the purpose of moving physically challenged guests into and out of the pool. May be powered by electricity or water. Also known as pool lift. |
| bool | wheelchair_rental | The property owns wheelchairs that it permits guests to borrow and use. Can be free of charge or for a fee. |

## Pets

| Type | Field | Definition |
|---|---|---|
| bool | pets_allowed | Household animals are allowed at the property and in the specific guest room of their owner. May or may not include dogs, cats, reptiles and/or fish. May or may not require a fee. Service animals are not considered to be pets, so not governed by this policy. |
| bool | pets_allowed_for_free | Household animals are allowed at the property and in the specific guest room of their owner for free. May or may not include dogs, cats, reptiles, and/or fish. |
| bool | dogs_allowed | Domesticated canines are permitted at the property and allowed to stay in the guest room of their owner. A fee may or may not be required. |
| bool | cats_allowed | Domesticated felines are permitted at the property and allowed to stay in the guest room of their owner. A fee may or may not be required. |

## PublicBath

| Type | Field | Definition |
|---|---|---|
| bool | onsen | The property has a Japanese-style bath with hot spring water and is in a common area where all guests can access it. The hot spring water can be naturally or artificially compounded. |
| bool | natural_onsen | The property has a type of Japanese-style public hot spring bath with naturally compounded hot spring water for relaxation and your health in a common area where all guests can access it. |
| bool | artificial_onsen | The property has a type of Japanese-style public hot spring bath with artificially compounded hot spring water for relaxation and your health in a common area where all guests can access it. |
| bool | public_bath | The property has a Japanese-style public (shared) bath in a common area where all guests can access it. |
| bool | open_air_bath | The property has a type of Japanese-style public bath that is not enclosed by walls or a roof in a common area where all guests can access it. |
| bool | private_bath | The property has a type of Japanese-style bath that is designed for use by a group of people like a family. Guests may need to make a reservation and/or pay a fee. |
| bool | jacuzzi | The property has a hot tub containing bubbling water maintained at a higher temperature and circulated by aerating jets for the purpose of soaking, relaxation, and hydrotherapy in public baths. |
| bool | water_bath | The property has a type of bath in which guests' bodies are submerged in cooler water. |
| bool | bedrock_bath | The property has a type of bath in which guests lie down on heated bedrock. |
| bool | mixed_bathing | The property has a type of public bath in which both men and women bathe together in the same area typically with swimsuits. |

## GuestUnitType

| Type | Field | Definition |
|---|---|---|
| repeated string | code | REQUIRED. Unit or room code identifiers for a single GuestUnitType. Each code must be unique within a Lodging. |
| string | name | REQUIRED. Short name of the GuestUnitType. Target less than 50 chars for English version. |
| [UnitTier](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#unittier) | tier | Standard or deluxe. A non-standard room tier is only permitted if at least one other unit type is of a lower tier. |
| int32 | max_number_of_occupants |   |
| int32 | max_number_of_adult_occupants |   |
| int32 | max_number_of_child_occupants |   |
| bool | private_home |   |
| bool | suite |   |
| bool | bungalow_or_villa |   |
| bool | executive_floor |   |
| bool | connecting_unit_available |   |
| [ViewsFromUnit](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#viewsfromunit) | views |   |
| [LivingArea](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#livingarea) | total_living_areas |   |

## ViewsFromUnit

| Type | Field | Definition |
|---|---|---|
| bool | view_of_beach |   |
| bool | view_of_city |   |
| bool | view_of_garden |   |
| bool | view_of_lake |   |
| bool | view_of_landmark |   |
| bool | view_of_ocean |   |
| bool | view_of_pool |   |
| bool | view_of_valley |   |

## LivingArea

| Type | Field | Definition |
|---|---|---|
| [LivingAreaLayout](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#livingarealayout) | layout |   |
| [LivingAreaFeatures](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#livingareafeatures) | features |   |
| [LivingAreaEating](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#livingareaeating) | eating |   |
| [LivingAreaSleeping](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#livingareasleeping) | sleeping |   |
| [LivingAreaAccessibility](https://developers.google.com/hotels/hotel-content/proto-reference/lodging-schema#livingareaaccessibility) | accessibility |   |

## LivingAreaLayout

| Type | Field | Definition |
|---|---|---|
| float | living_area_sq_meters |   |
| bool | stairs |   |
| bool | loft |   |
| bool | non_smoking |   |
| bool | patio |   |
| bool | balcony |   |

## LivingAreaFeatures

| Type | Field | Definition |
|---|---|---|
| bool | private_bathroom |   |
| bool | toilet |   |
| bool | bidet |   |
| bool | shower |   |
| bool | bathtub |   |
| bool | hairdryer |   |
| bool | washer |   |
| bool | dryer |   |
| bool | ironing_equipment |   |
| bool | universal_power_adapters |   |
| bool | air_conditioning |   |
| bool | heating |   |
| bool | fireplace |   |
| bool | tv |   |
| bool | tv_with_casting |   |
| bool | tv_with_streaming |   |
| bool | pay_per_view_movies |   |
| bool | in_unit_safe |   |
| bool | electronic_room_key |   |
| bool | in_unit_wifi_available |   |

## LivingAreaEating

| Type | Field | Definition |
|---|---|---|
| bool | kitchen_available |   |
| bool | refrigerator |   |
| bool | dishwasher |   |
| bool | stove |   |
| bool | oven |   |
| bool | cookware |   |
| bool | sink |   |
| bool | microwave |   |
| bool | toaster |   |
| bool | indoor_grill |   |
| bool | outdoor_grill |   |
| bool | minibar |   |
| bool | snackbar |   |
| bool | coffee_maker |   |
| bool | kettle |   |
| bool | tea_station |   |

## LivingAreaSleeping

| Type | Field | Definition |
|---|---|---|
| int32 | number_of_beds |   |
| int32 | king_beds |   |
| int32 | queen_beds |   |
| int32 | double_beds |   |
| int32 | single_or_twin_beds |   |
| int32 | sofa_beds |   |
| int32 | bunk_beds |   |
| int32 | other_beds |   |
| bool | roll_away_beds |   |
| int32 | roll_away_bed_count |   |
| bool | cribs |   |
| int32 | crib_count |   |
| bool | hypoallergenic_bedding |   |
| bool | synthetic_pillows |   |
| bool | memory_foam_pillows |   |
| bool | feather_pillows |   |

## LivingAreaAccessibility

| Type | Field | Definition |
|---|---|---|
| bool | mobility_accessible_unit |   |
| bool | ada_compliant_unit |   |
| bool | hearing_accessible_unit |   |
| bool | mobility_accessible_shower |   |
| bool | mobility_accessible_bathtub |   |
| bool | mobility_accessible_toilet |   |
| bool | hearing_accessible_doorbell |   |
| bool | hearing_accessible_fire_alarm |   |

## UnitTier

| Value | Definition |
|---|---|
| DEFAULT_STANDARD | Standard. The basic unit tier for this Lodging. |
| DELUXE | Deluxe or Superior. Only allowed if another unit type is a standard tier. |

## Exception

| Value | Definition |
|---|---|
| UNSPECIFIED_REASON | Only use this if the factual information cannot be represented by the relevant proto field. i.e. a service is only available during some days of the week, or an amenity is only available seasonally. |