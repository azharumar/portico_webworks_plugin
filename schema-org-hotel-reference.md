# Schema.org JSON-LD Reference — Hotel Websites
**For:** Portico Webworks  
**Scope:** Single-property hotel website (WordPress + Portico Hotel Manager plugin)  
**Purpose:** Complete schema markup reference for all applicable pages. Drop each block into a `<script type="application/ld+json">` tag in the page `<head>`.

---

## Table of Contents

1. [Schema priority order](#1-schema-priority-order)
2. [Core types used](#2-core-types-used)
3. [Homepage — LodgingBusiness](#3-homepage--lodgingbusiness)
4. [Rooms overview — `ItemList`](#4-rooms-overview--itemlist)
5. [Individual room page — `HotelRoom` + `Product` (MTE)](#5-individual-room-page--hotelroom--product-mte)
6. [Dining overview — `ItemList`](#6-dining-overview--itemlist)
7. [Individual restaurant — `FoodEstablishment`](#7-individual-restaurant--foodestablishment)
8. [Spa page — `HealthAndBeautyBusiness`](#8-spa-page--healthandbeautybusiness)
9. [Meetings overview — `ItemList`](#9-meetings-overview--itemlist)
10. [Individual venue — `MeetingRoom` + `Product` (MTE)](#10-individual-venue--meetingroom--product-mte)
11. [Experiences overview — `ItemList`](#11-experiences-overview--itemlist)
12. [Individual experience — `Product`](#12-individual-experience--product)
13. [Offers overview — `ItemList`](#13-offers-overview--itemlist)
14. [Individual offer page — `Offer`](#14-individual-offer-page--offer)
15. [Events overview — `ItemList`](#15-events-overview--itemlist)
16. [Individual event — `Event`](#16-individual-event--event)
17. [Nearby attractions — `TouristAttraction`](#17-nearby-attractions--touristattraction)
18. [FAQ page — `FAQPage`](#18-faq-page--faqpage)
19. [Reviews page — `AggregateRating`](#19-reviews-page--aggregaterating)
20. [Blog post — `BlogPosting`](#20-blog-post--blogposting)
21. [Policies page — `ItemList`](#21-policies-page--itemlist)
22. [Contact page — `LodgingBusiness` (contact fragment)](#22-contact-page--lodgingbusiness-contact-fragment)
23. [Book Direct page — `WebPage` + `SpecialAnnouncement`](#23-book-direct-page--webpage)
24. [Breadcrumb — all inner pages](#24-breadcrumb--all-inner-pages)
25. [WordPress implementation notes](#25-wordpress-implementation-notes)
26. [Placeholder key](#26-placeholder-key)

---

## 1. Schema priority order

Implement in this sequence. The first six have the highest impact on Google rich results and AI engine visibility.

| Priority | Page | Schema type | Rich result potential |
|---|---|---|---|
| 1 | `/faq` | `FAQPage` | FAQ accordion in SERPs |
| 2 | `/rooms/{slug}` | `HotelRoom` + `Product` | Room pricing in hotel pack |
| 3 | `/offers/{slug}` | `Offer` | Deal/price rich result |
| 4 | `/events/{slug}` | `Event` | Event rich result |
| 5 | `/reviews` | `AggregateRating` | Star rating in SERPs |
| 6 | `/` (home) | `LodgingBusiness` | Hotel knowledge panel |
| 7 | `/dining/{slug}` | `FoodEstablishment` | Restaurant panel |
| 8 | `/blog/{slug}` | `BlogPosting` | Article rich result |
| 9 | All inner pages | `BreadcrumbList` | Breadcrumb in SERPs |

---

## 2. Core types used

| Schema type | Used on | Inherits from |
|---|---|---|
| `Hotel` | Homepage | `LodgingBusiness` → `LocalBusiness` → `Place` |
| `HotelRoom` | Room single pages | `Accommodation` → `Place` |
| `Product` | Room + meeting room (MTE only) | — |
| `Suite` | Suite room type | `Accommodation` → `Place` |
| `MeetingRoom` | Venue single pages | `Accommodation` → `Place` |
| `FoodEstablishment` | Restaurant pages | `LocalBusiness` → `Place` |
| `HealthAndBeautyBusiness` | Spa page | `LocalBusiness` → `Place` |
| `Event` | Event single pages | — |
| `TouristAttraction` | Nearby attraction items | `Place` |
| `Offer` | Offer pages, room pages | — |
| `FAQPage` + `Question` + `Answer` | FAQ page | — |
| `AggregateRating` | Reviews page | — |
| `BreadcrumbList` + `ListItem` | All inner pages | — |
| `BlogPosting` | Blog post pages | `Article` → `CreativeWork` |
| `ItemList` + `ListItem` | Archive/overview pages | — |
| `PostalAddress` | Address fields | — |
| `GeoCoordinates` | Map/location | — |
| `LocationFeatureSpecification` | Amenity features | `PropertyValue` |
| `QuantitativeValue` | Occupancy, size, bed count | — |
| `UnitPriceSpecification` | Nightly rates | `PriceSpecification` |
| `Rating` | Star rating | — |

---

## 3. Homepage — LodgingBusiness

Apply on the site homepage (`/`). Use the full property graph there; trim only if the theme duplicates fields elsewhere.

```json
{
  "@context": "https://schema.org",
  "@type": "Hotel",
  "@id": "https://HOTEL_DOMAIN/#hotel",
  "name": "HOTEL_NAME",
  "alternateName": "HOTEL_SHORT_NAME",
  "description": "HOTEL_META_DESCRIPTION",
  "url": "https://HOTEL_DOMAIN/",
  "logo": {
    "@type": "ImageObject",
    "url": "https://HOTEL_DOMAIN/wp-content/uploads/LOGO_FILE.png",
    "width": 300,
    "height": 100
  },
  "image": [
    "https://HOTEL_DOMAIN/wp-content/uploads/HERO_IMAGE_1.jpg",
    "https://HOTEL_DOMAIN/wp-content/uploads/HERO_IMAGE_2.jpg"
  ],
  "telephone": "+91-PHONE_NUMBER",
  "email": "reservations@HOTEL_DOMAIN",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "STREET_ADDRESS",
    "addressLocality": "CITY",
    "addressRegion": "STATE",
    "postalCode": "PIN_CODE",
    "addressCountry": "IN"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": LAT,
    "longitude": LNG
  },
  "hasMap": "https://www.google.com/maps?q=LAT,LNG",
  "starRating": {
    "@type": "Rating",
    "ratingValue": "STAR_COUNT",
    "author": {
      "@type": "Organization",
      "name": "RATING_AUTHORITY"
    }
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "AGGREGATE_SCORE",
    "reviewCount": REVIEW_COUNT,
    "bestRating": "10",
    "worstRating": "1"
  },
  "priceRange": "INR PRICE_MIN–PRICE_MAX per night",
  "checkinTime": "14:00",
  "checkoutTime": "11:00",
  "numberOfRooms": TOTAL_ROOM_COUNT,
  "petsAllowed": false,
  "smokingAllowed": false,
  "availableLanguage": [
    { "@type": "Language", "name": "English" },
    { "@type": "Language", "name": "REGIONAL_LANGUAGE" }
  ],
  "amenityFeature": [
    {
      "@type": "LocationFeatureSpecification",
      "name": "Swimming Pool",
      "value": true
    },
    {
      "@type": "LocationFeatureSpecification",
      "name": "Free WiFi",
      "value": true
    },
    {
      "@type": "LocationFeatureSpecification",
      "name": "Fitness Centre",
      "value": true
    },
    {
      "@type": "LocationFeatureSpecification",
      "name": "Restaurant",
      "value": true
    },
    {
      "@type": "LocationFeatureSpecification",
      "name": "Spa",
      "value": true
    },
    {
      "@type": "LocationFeatureSpecification",
      "name": "Airport Transfer",
      "value": true
    },
    {
      "@type": "LocationFeatureSpecification",
      "name": "24-hour Front Desk",
      "value": true
    },
    {
      "@type": "LocationFeatureSpecification",
      "name": "Parking",
      "value": true
    }
  ],
  "openingHoursSpecification": {
    "@type": "OpeningHoursSpecification",
    "dayOfWeek": [
      "Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"
    ],
    "opens": "00:00",
    "closes": "23:59"
  },
  "sameAs": [
    "https://www.tripadvisor.com/TRIPADVISOR_PROFILE_URL",
    "https://www.facebook.com/FACEBOOK_PAGE",
    "https://www.instagram.com/INSTAGRAM_HANDLE",
    "https://www.linkedin.com/company/LINKEDIN_SLUG"
  ]
}
```

> **Note:** Use `@id` with a URL fragment (`#hotel`) so other schemas on the site can reference this entity via `"containedInPlace": { "@id": "https://HOTEL_DOMAIN/#hotel" }`.

---

## 4. Rooms overview — `ItemList`

Apply to `/rooms`. Lists all room types with links to individual pages.

```json
{
  "@context": "https://schema.org",
  "@type": "ItemList",
  "name": "Rooms & Suites at HOTEL_NAME",
  "url": "https://HOTEL_DOMAIN/rooms/",
  "numberOfItems": ROOM_TYPE_COUNT,
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "url": "https://HOTEL_DOMAIN/rooms/ROOM_SLUG_1/",
      "name": "ROOM_TYPE_NAME_1"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "url": "https://HOTEL_DOMAIN/rooms/ROOM_SLUG_2/",
      "name": "ROOM_TYPE_NAME_2"
    },
    {
      "@type": "ListItem",
      "position": 3,
      "url": "https://HOTEL_DOMAIN/rooms/ROOM_SLUG_3/",
      "name": "ROOM_TYPE_NAME_3"
    }
  ]
}
```

---

## 5. Individual room page — `HotelRoom` + `Product` (MTE)

Apply to `/rooms/{slug}`. The MTE (`@type` array) is **mandatory** to attach an `Offer`.

```json
{
  "@context": "https://schema.org",
  "@type": ["HotelRoom", "Product"],
  "@id": "https://HOTEL_DOMAIN/rooms/ROOM_SLUG/#room",
  "name": "ROOM_TYPE_NAME",
  "description": "ROOM_DESCRIPTION",
  "url": "https://HOTEL_DOMAIN/rooms/ROOM_SLUG/",
  "image": [
    "https://HOTEL_DOMAIN/wp-content/uploads/ROOM_IMAGE_1.jpg",
    "https://HOTEL_DOMAIN/wp-content/uploads/ROOM_IMAGE_2.jpg"
  ],
  "containedInPlace": {
    "@id": "https://HOTEL_DOMAIN/#hotel"
  },
  "bed": {
    "@type": "BedDetails",
    "typeOfBed": "BED_TYPE",
    "numberOfBeds": BED_COUNT
  },
  "occupancy": {
    "@type": "QuantitativeValue",
    "minValue": 1,
    "maxValue": MAX_OCCUPANCY,
    "unitCode": "IE"
  },
  "floorSize": {
    "@type": "QuantitativeValue",
    "value": ROOM_SIZE_SQM,
    "unitCode": "MTK"
  },
  "numberOfRooms": 1,
  "petsAllowed": false,
  "smokingAllowed": false,
  "amenityFeature": [
    {
      "@type": "LocationFeatureSpecification",
      "name": "Air Conditioning",
      "value": true
    },
    {
      "@type": "LocationFeatureSpecification",
      "name": "Free WiFi",
      "value": true
    },
    {
      "@type": "LocationFeatureSpecification",
      "name": "Flat-screen TV",
      "value": true
    },
    {
      "@type": "LocationFeatureSpecification",
      "name": "Mini Bar",
      "value": true
    },
    {
      "@type": "LocationFeatureSpecification",
      "name": "Safe",
      "value": true
    },
    {
      "@type": "LocationFeatureSpecification",
      "name": "Room Service",
      "value": true
    },
    {
      "@type": "LocationFeatureSpecification",
      "name": "VIEW_TYPE",
      "value": true
    }
  ],
  "offers": {
    "@type": "Offer",
    "url": "https://HOTEL_DOMAIN/book/?room=ROOM_SLUG",
    "priceCurrency": "INR",
    "priceSpecification": {
      "@type": "UnitPriceSpecification",
      "price": RACK_RATE,
      "priceCurrency": "INR",
      "unitCode": "DAY",
      "minPrice": MIN_PRICE,
      "maxPrice": MAX_PRICE
    },
    "availability": "https://schema.org/InStock",
    "validFrom": "YYYY-MM-DD",
    "seller": {
      "@id": "https://HOTEL_DOMAIN/#hotel"
    }
  }
}
```

### Suite variant

For `/rooms/{suite-slug}` — use `Suite` instead of `HotelRoom`:

```json
{
  "@context": "https://schema.org",
  "@type": ["Suite", "Product"],
  "@id": "https://HOTEL_DOMAIN/rooms/SUITE_SLUG/#suite",
  "name": "SUITE_NAME",
  "numberOfRooms": SUITE_ROOM_COUNT,
  "bed": [
    {
      "@type": "BedDetails",
      "typeOfBed": "King Bed",
      "numberOfBeds": 1
    }
  ],
  "occupancy": {
    "@type": "QuantitativeValue",
    "minValue": 1,
    "maxValue": MAX_OCCUPANCY,
    "unitCode": "IE"
  },
  "offers": {
    "@type": "Offer",
    "priceSpecification": {
      "@type": "UnitPriceSpecification",
      "price": SUITE_RACK_RATE,
      "priceCurrency": "INR",
      "unitCode": "DAY"
    }
  }
}
```

### Room with seasonal pricing

When the room has peak/off-peak rates, use an `offers` array:

```json
"offers": [
  {
    "@type": "Offer",
    "name": "Peak Season Rate",
    "availabilityStarts": "PEAK_START_DATE",
    "availabilityEnds": "PEAK_END_DATE",
    "priceSpecification": {
      "@type": "UnitPriceSpecification",
      "price": PEAK_RATE,
      "priceCurrency": "INR",
      "unitCode": "DAY"
    }
  },
  {
    "@type": "Offer",
    "name": "Off-Peak Rate",
    "availabilityStarts": "OFFPEAK_START_DATE",
    "availabilityEnds": "OFFPEAK_END_DATE",
    "priceSpecification": {
      "@type": "UnitPriceSpecification",
      "price": OFFPEAK_RATE,
      "priceCurrency": "INR",
      "unitCode": "DAY"
    }
  }
]
```

### Room with breakfast bundle

```json
"offers": {
  "@type": "Offer",
  "name": "Bed & Breakfast Rate",
  "priceSpecification": {
    "@type": "UnitPriceSpecification",
    "price": BB_RATE,
    "priceCurrency": "INR",
    "unitCode": "DAY"
  },
  "includesObject": [
    {
      "@type": "TypeAndQuantityNode",
      "typeOfGood": {
        "@type": "HotelRoom",
        "name": "ROOM_TYPE_NAME"
      }
    },
    {
      "@type": "TypeAndQuantityNode",
      "typeOfGood": {
        "@type": "FoodService",
        "name": "Breakfast"
      }
    }
  ]
}
```

---

## 6. Dining overview — `ItemList`

Apply to `/dining`.

```json
{
  "@context": "https://schema.org",
  "@type": "ItemList",
  "name": "Dining at HOTEL_NAME",
  "url": "https://HOTEL_DOMAIN/dining/",
  "numberOfItems": RESTAURANT_COUNT,
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "url": "https://HOTEL_DOMAIN/dining/RESTAURANT_SLUG_1/",
      "name": "RESTAURANT_NAME_1"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "url": "https://HOTEL_DOMAIN/dining/RESTAURANT_SLUG_2/",
      "name": "RESTAURANT_NAME_2"
    }
  ]
}
```

---

## 7. Individual restaurant — `FoodEstablishment`

Apply to `/dining/{slug}`.

```json
{
  "@context": "https://schema.org",
  "@type": "Restaurant",
  "@id": "https://HOTEL_DOMAIN/dining/RESTAURANT_SLUG/#restaurant",
  "name": "RESTAURANT_NAME",
  "description": "RESTAURANT_DESCRIPTION",
  "url": "https://HOTEL_DOMAIN/dining/RESTAURANT_SLUG/",
  "image": "https://HOTEL_DOMAIN/wp-content/uploads/RESTAURANT_IMAGE.jpg",
  "servesCuisine": "CUISINE_TYPE",
  "priceRange": "INR PRICE_MIN–PRICE_MAX",
  "hasMenu": "https://HOTEL_DOMAIN/dining/RESTAURANT_SLUG/menu/",
  "acceptsReservations": true,
  "telephone": "+91-RESTAURANT_PHONE",
  "containedInPlace": {
    "@id": "https://HOTEL_DOMAIN/#hotel"
  },
  "address": {
    "@id": "https://HOTEL_DOMAIN/#hotel"
  },
  "openingHoursSpecification": [
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday"],
      "opens": "07:00",
      "closes": "23:00"
    },
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Saturday","Sunday"],
      "opens": "07:30",
      "closes": "23:30"
    }
  ],
  "amenityFeature": [
    {
      "@type": "LocationFeatureSpecification",
      "name": "Outdoor Seating",
      "value": true
    }
  ]
}
```

---

## 8. Spa page — `HealthAndBeautyBusiness`

Apply to `/spa`.

```json
{
  "@context": "https://schema.org",
  "@type": "HealthAndBeautyBusiness",
  "@id": "https://HOTEL_DOMAIN/spa/#spa",
  "name": "SPA_NAME",
  "description": "SPA_DESCRIPTION",
  "url": "https://HOTEL_DOMAIN/spa/",
  "image": "https://HOTEL_DOMAIN/wp-content/uploads/SPA_IMAGE.jpg",
  "containedInPlace": {
    "@id": "https://HOTEL_DOMAIN/#hotel"
  },
  "telephone": "+91-SPA_PHONE",
  "openingHoursSpecification": [
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": [
        "Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"
      ],
      "opens": "09:00",
      "closes": "21:00"
    }
  ],
  "amenityFeature": [
    {
      "@type": "LocationFeatureSpecification",
      "name": "Steam Room",
      "value": true
    },
    {
      "@type": "LocationFeatureSpecification",
      "name": "Jacuzzi",
      "value": true
    }
  ],
  "hasOfferCatalog": {
    "@type": "OfferCatalog",
    "name": "Spa Treatments",
    "url": "https://HOTEL_DOMAIN/spa/treatments/"
  }
}
```

---

## 9. Meetings overview — `ItemList`

Apply to `/meetings`.

```json
{
  "@context": "https://schema.org",
  "@type": "ItemList",
  "name": "Meetings & Events Venues at HOTEL_NAME",
  "url": "https://HOTEL_DOMAIN/meetings/",
  "numberOfItems": VENUE_COUNT,
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "url": "https://HOTEL_DOMAIN/meetings/VENUE_SLUG_1/",
      "name": "VENUE_NAME_1"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "url": "https://HOTEL_DOMAIN/meetings/VENUE_SLUG_2/",
      "name": "VENUE_NAME_2"
    }
  ]
}
```

---

## 10. Individual venue — `MeetingRoom` + `Product` (MTE)

Apply to `/meetings/{slug}`. MTE required to attach an `Offer` for venue hire pricing.

```json
{
  "@context": "https://schema.org",
  "@type": ["MeetingRoom", "Product"],
  "@id": "https://HOTEL_DOMAIN/meetings/VENUE_SLUG/#venue",
  "name": "VENUE_NAME",
  "description": "VENUE_DESCRIPTION",
  "url": "https://HOTEL_DOMAIN/meetings/VENUE_SLUG/",
  "image": "https://HOTEL_DOMAIN/wp-content/uploads/VENUE_IMAGE.jpg",
  "containedInPlace": {
    "@id": "https://HOTEL_DOMAIN/#hotel"
  },
  "floorSize": {
    "@type": "QuantitativeValue",
    "value": VENUE_SIZE_SQM,
    "unitCode": "MTK"
  },
  "maximumAttendeeCapacity": MAX_CAPACITY,
  "amenityFeature": [
    {
      "@type": "LocationFeatureSpecification",
      "name": "AV Equipment",
      "value": true
    },
    {
      "@type": "LocationFeatureSpecification",
      "name": "High-speed WiFi",
      "value": true
    },
    {
      "@type": "LocationFeatureSpecification",
      "name": "Natural Light",
      "value": true
    },
    {
      "@type": "LocationFeatureSpecification",
      "name": "Air Conditioning",
      "value": true
    }
  ],
  "offers": {
    "@type": "Offer",
    "name": "Full-day venue hire",
    "url": "https://HOTEL_DOMAIN/meetings/VENUE_SLUG/",
    "priceSpecification": {
      "@type": "UnitPriceSpecification",
      "price": VENUE_FULLDAY_RATE,
      "priceCurrency": "INR",
      "unitCode": "DAY"
    },
    "seller": {
      "@id": "https://HOTEL_DOMAIN/#hotel"
    }
  }
}
```

---

## 11. Experiences overview — `ItemList`

Apply to `/experiences`.

```json
{
  "@context": "https://schema.org",
  "@type": "ItemList",
  "name": "Experiences at HOTEL_NAME",
  "url": "https://HOTEL_DOMAIN/experiences/",
  "numberOfItems": EXPERIENCE_COUNT,
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "url": "https://HOTEL_DOMAIN/experiences/EXPERIENCE_SLUG_1/",
      "name": "EXPERIENCE_NAME_1"
    }
  ]
}
```

---

## 12. Individual experience — `Product`

Apply to `/experiences/{slug}`. No specific schema.org type for hotel experiences; `Product` with an `Offer` is the correct pattern.

```json
{
  "@context": "https://schema.org",
  "@type": "Product",
  "@id": "https://HOTEL_DOMAIN/experiences/EXPERIENCE_SLUG/#experience",
  "name": "EXPERIENCE_NAME",
  "description": "EXPERIENCE_DESCRIPTION",
  "url": "https://HOTEL_DOMAIN/experiences/EXPERIENCE_SLUG/",
  "image": "https://HOTEL_DOMAIN/wp-content/uploads/EXPERIENCE_IMAGE.jpg",
  "brand": {
    "@type": "Brand",
    "name": "HOTEL_NAME"
  },
  "offers": {
    "@type": "Offer",
    "url": "BOOKING_URL",
    "price": EXPERIENCE_PRICE,
    "priceCurrency": "INR",
    "availability": "https://schema.org/InStock",
    "seller": {
      "@id": "https://HOTEL_DOMAIN/#hotel"
    }
  }
}
```

---

## 13. Offers overview — `ItemList`

Apply to `/offers`.

```json
{
  "@context": "https://schema.org",
  "@type": "ItemList",
  "name": "Special Offers at HOTEL_NAME",
  "url": "https://HOTEL_DOMAIN/offers/",
  "numberOfItems": OFFER_COUNT,
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "url": "https://HOTEL_DOMAIN/offers/OFFER_SLUG_1/",
      "name": "OFFER_NAME_1"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "url": "https://HOTEL_DOMAIN/offers/OFFER_SLUG_2/",
      "name": "OFFER_NAME_2"
    }
  ]
}
```

---

## 14. Individual offer page — `Offer`

Apply to `/offers/{slug}`.

```json
{
  "@context": "https://schema.org",
  "@type": "Offer",
  "@id": "https://HOTEL_DOMAIN/offers/OFFER_SLUG/#offer",
  "name": "OFFER_NAME",
  "description": "OFFER_DESCRIPTION",
  "url": "https://HOTEL_DOMAIN/offers/OFFER_SLUG/",
  "image": "https://HOTEL_DOMAIN/wp-content/uploads/OFFER_IMAGE.jpg",
  "priceCurrency": "INR",
  "price": OFFER_PRICE,
  "priceSpecification": {
    "@type": "UnitPriceSpecification",
    "price": OFFER_PRICE,
    "priceCurrency": "INR",
    "unitCode": "DAY"
  },
  "validFrom": "OFFER_START_DATE",
  "validThrough": "OFFER_END_DATE",
  "availability": "https://schema.org/InStock",
  "itemOffered": {
    "@type": ["HotelRoom", "Product"],
    "name": "INCLUDED_ROOM_TYPE",
    "containedInPlace": {
      "@id": "https://HOTEL_DOMAIN/#hotel"
    }
  },
  "seller": {
    "@id": "https://HOTEL_DOMAIN/#hotel"
  },
  "offeredBy": {
    "@id": "https://HOTEL_DOMAIN/#hotel"
  }
}
```

---

## 15. Events overview — `ItemList`

Apply to `/events`.

```json
{
  "@context": "https://schema.org",
  "@type": "ItemList",
  "name": "Upcoming Events at HOTEL_NAME",
  "url": "https://HOTEL_DOMAIN/events/",
  "numberOfItems": EVENT_COUNT,
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "url": "https://HOTEL_DOMAIN/events/EVENT_SLUG_1/",
      "name": "EVENT_NAME_1"
    }
  ]
}
```

---

## 16. Individual event — `Event`

Apply to `/events/{slug}`. High-priority for Google Event rich results.

```json
{
  "@context": "https://schema.org",
  "@type": "Event",
  "@id": "https://HOTEL_DOMAIN/events/EVENT_SLUG/#event",
  "name": "EVENT_NAME",
  "description": "EVENT_DESCRIPTION",
  "url": "https://HOTEL_DOMAIN/events/EVENT_SLUG/",
  "image": "https://HOTEL_DOMAIN/wp-content/uploads/EVENT_IMAGE.jpg",
  "startDate": "YYYY-MM-DDTHH:MM:SS+05:30",
  "endDate": "YYYY-MM-DDTHH:MM:SS+05:30",
  "eventStatus": "https://schema.org/EventScheduled",
  "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
  "location": {
    "@type": "Place",
    "name": "VENUE_NAME",
    "address": {
      "@id": "https://HOTEL_DOMAIN/#hotel"
    },
    "containedInPlace": {
      "@id": "https://HOTEL_DOMAIN/#hotel"
    }
  },
  "organizer": {
    "@type": "Organization",
    "name": "HOTEL_NAME",
    "url": "https://HOTEL_DOMAIN/"
  },
  "maximumAttendeeCapacity": MAX_CAPACITY,
  "offers": {
    "@type": "Offer",
    "url": "https://HOTEL_DOMAIN/events/EVENT_SLUG/",
    "price": EVENT_TICKET_PRICE,
    "priceCurrency": "INR",
    "availability": "https://schema.org/InStock",
    "validFrom": "YYYY-MM-DD"
  }
}
```

> For free events set `"price": 0` and `"availability": "https://schema.org/InStock"`.  
> For private events omit the `offers` block.

---

## 17. Nearby attractions — `TouristAttraction`

Apply to `/location/nearby`. Render as `ItemList` wrapping individual `TouristAttraction` entities.

```json
{
  "@context": "https://schema.org",
  "@type": "ItemList",
  "name": "Nearby Attractions",
  "url": "https://HOTEL_DOMAIN/location/nearby/",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "item": {
        "@type": "TouristAttraction",
        "name": "ATTRACTION_NAME_1",
        "description": "ATTRACTION_DESCRIPTION_1",
        "geo": {
          "@type": "GeoCoordinates",
          "latitude": ATTRACTION_LAT,
          "longitude": ATTRACTION_LNG
        },
        "touristType": "ATTRACTION_CATEGORY"
      }
    },
    {
      "@type": "ListItem",
      "position": 2,
      "item": {
        "@type": "TouristAttraction",
        "name": "ATTRACTION_NAME_2",
        "description": "ATTRACTION_DESCRIPTION_2",
        "geo": {
          "@type": "GeoCoordinates",
          "latitude": ATTRACTION_LAT_2,
          "longitude": ATTRACTION_LNG_2
        }
      }
    }
  ]
}
```

---

## 18. FAQ page — `FAQPage`

Apply to `/faq`. Highest priority for AI engine visibility. Each `Question` must have a direct `Answer` with `acceptedAnswer`.

```json
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "name": "Frequently Asked Questions — HOTEL_NAME",
  "url": "https://HOTEL_DOMAIN/faq/",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "What time is check-in and check-out at HOTEL_NAME?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Check-in is from CHECK_IN_TIME. Check-out is by CHECK_OUT_TIME. Early check-in and late check-out are subject to availability and may attract an additional charge."
      }
    },
    {
      "@type": "Question",
      "name": "Does HOTEL_NAME offer free WiFi?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes, complimentary high-speed WiFi is available throughout the hotel, including all guest rooms, restaurants, and public areas."
      }
    },
    {
      "@type": "Question",
      "name": "Is parking available at HOTEL_NAME?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "PARKING_ANSWER"
      }
    },
    {
      "@type": "Question",
      "name": "What is the cancellation policy at HOTEL_NAME?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "CANCELLATION_POLICY_TEXT"
      }
    },
    {
      "@type": "Question",
      "name": "Are children allowed at HOTEL_NAME?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "CHILD_POLICY_TEXT"
      }
    },
    {
      "@type": "Question",
      "name": "Does HOTEL_NAME have a swimming pool?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "POOL_ANSWER"
      }
    },
    {
      "@type": "Question",
      "name": "How far is HOTEL_NAME from the nearest airport?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "HOTEL_NAME is approximately DISTANCE_KM km from AIRPORT_NAME. The drive takes approximately DRIVE_TIME minutes. We offer airport transfers — please contact reservations at reservations@HOTEL_DOMAIN to arrange."
      }
    },
    {
      "@type": "Question",
      "name": "Is it cheaper to book directly with HOTEL_NAME?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes. Booking directly through our website guarantees the best available rate, plus exclusive benefits including DIRECT_BENEFIT_1, DIRECT_BENEFIT_2, and DIRECT_BENEFIT_3."
      }
    }
  ]
}
```

---

## 19. Reviews page — `AggregateRating`

Apply to `/reviews`. Since Google and TripAdvisor data is third-party, wrap the aggregate in a reference to the hotel entity.

```json
{
  "@context": "https://schema.org",
  "@type": "Hotel",
  "@id": "https://HOTEL_DOMAIN/#hotel",
  "name": "HOTEL_NAME",
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "AGGREGATE_SCORE",
    "reviewCount": TOTAL_REVIEW_COUNT,
    "bestRating": "10",
    "worstRating": "1",
    "ratingExplanation": "Based on verified guest reviews from Google and TripAdvisor"
  },
  "review": [
    {
      "@type": "Review",
      "author": {
        "@type": "Person",
        "name": "REVIEWER_NAME"
      },
      "datePublished": "REVIEW_DATE",
      "reviewRating": {
        "@type": "Rating",
        "ratingValue": "INDIVIDUAL_SCORE",
        "bestRating": "10",
        "worstRating": "1"
      },
      "reviewBody": "REVIEW_TEXT_EXCERPT"
    }
  ]
}
```

> Add 3–5 individual `review` items for richer markup. Use real guest reviews with permission.

---

## 20. Blog post — `BlogPosting`

Apply to `/blog/{slug}`.

```json
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "@id": "https://HOTEL_DOMAIN/blog/POST_SLUG/#post",
  "headline": "POST_TITLE",
  "description": "POST_META_DESCRIPTION",
  "url": "https://HOTEL_DOMAIN/blog/POST_SLUG/",
  "datePublished": "YYYY-MM-DD",
  "dateModified": "YYYY-MM-DD",
  "image": {
    "@type": "ImageObject",
    "url": "https://HOTEL_DOMAIN/wp-content/uploads/POST_FEATURED_IMAGE.jpg",
    "width": 1200,
    "height": 630
  },
  "author": {
    "@type": "Organization",
    "name": "HOTEL_NAME",
    "url": "https://HOTEL_DOMAIN/"
  },
  "publisher": {
    "@type": "Organization",
    "name": "HOTEL_NAME",
    "logo": {
      "@type": "ImageObject",
      "url": "https://HOTEL_DOMAIN/wp-content/uploads/LOGO_FILE.png"
    }
  },
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "https://HOTEL_DOMAIN/blog/POST_SLUG/"
  },
  "about": {
    "@type": "Place",
    "name": "DESTINATION_NAME"
  },
  "keywords": "KEYWORD_1, KEYWORD_2, KEYWORD_3",
  "articleSection": "POST_CATEGORY",
  "wordCount": WORD_COUNT,
  "inLanguage": "en-IN"
}
```

---

## 21. Policies page — `ItemList`

Apply to `/policies`. No direct schema type for hotel policies; use `ItemList` with `WebPageElement` items.

```json
{
  "@context": "https://schema.org",
  "@type": "WebPage",
  "name": "Hotel Policies — HOTEL_NAME",
  "url": "https://HOTEL_DOMAIN/policies/",
  "description": "Policies at HOTEL_NAME including cancellation, check-in, pet, child, and payment policies.",
  "about": {
    "@id": "https://HOTEL_DOMAIN/#hotel"
  },
  "speakable": {
    "@type": "SpeakableSpecification",
    "cssSelector": [".policy-cancellation", ".policy-checkin", ".policy-child"]
  }
}
```

> The `speakable` property tells voice assistants and AI engines which sections contain answer-ready content — apply CSS selectors to your policy text blocks.

---

## 22. Contact page — `LodgingBusiness` (contact fragment)

Apply to `/contact`. Reference the main hotel entity; don't duplicate the full block.

```json
{
  "@context": "https://schema.org",
  "@type": "Hotel",
  "@id": "https://HOTEL_DOMAIN/#hotel",
  "name": "HOTEL_NAME",
  "telephone": "+91-MAIN_PHONE",
  "email": "info@HOTEL_DOMAIN",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "STREET_ADDRESS",
    "addressLocality": "CITY",
    "addressRegion": "STATE",
    "postalCode": "PIN_CODE",
    "addressCountry": "IN"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": LAT,
    "longitude": LNG
  },
  "contactPoint": [
    {
      "@type": "ContactPoint",
      "contactType": "reservations",
      "telephone": "+91-RESERVATIONS_PHONE",
      "email": "reservations@HOTEL_DOMAIN",
      "availableLanguage": ["English", "REGIONAL_LANGUAGE"]
    },
    {
      "@type": "ContactPoint",
      "contactType": "customer support",
      "telephone": "+91-FRONT_DESK_PHONE",
      "contactOption": "TollFree",
      "hoursAvailable": {
        "@type": "OpeningHoursSpecification",
        "opens": "00:00",
        "closes": "23:59",
        "dayOfWeek": [
          "Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"
        ]
      }
    }
  ]
}
```

---

## 23. Book Direct page — `WebPage`

Apply to `/book-direct`. Uses `WebPage` to surface direct booking benefits for AI engines.

```json
{
  "@context": "https://schema.org",
  "@type": "WebPage",
  "name": "Why Book Direct — HOTEL_NAME",
  "url": "https://HOTEL_DOMAIN/book-direct/",
  "description": "Book directly with HOTEL_NAME for the best rate guarantee and exclusive benefits not available on OTAs.",
  "about": {
    "@id": "https://HOTEL_DOMAIN/#hotel"
  },
  "mainEntity": {
    "@type": "ItemList",
    "name": "Direct Booking Benefits",
    "itemListElement": [
      {
        "@type": "ListItem",
        "position": 1,
        "name": "DIRECT_BENEFIT_1"
      },
      {
        "@type": "ListItem",
        "position": 2,
        "name": "DIRECT_BENEFIT_2"
      },
      {
        "@type": "ListItem",
        "position": 3,
        "name": "DIRECT_BENEFIT_3"
      },
      {
        "@type": "ListItem",
        "position": 4,
        "name": "Best rate guarantee"
      }
    ]
  }
}
```

---

## 24. Breadcrumb — all inner pages

Apply to every page except the homepage. Generates breadcrumb trail in Google SERPs.

```json
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Home",
      "item": "https://HOTEL_DOMAIN/"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "PARENT_PAGE_NAME",
      "item": "https://HOTEL_DOMAIN/PARENT_SLUG/"
    },
    {
      "@type": "ListItem",
      "position": 3,
      "name": "CURRENT_PAGE_NAME",
      "item": "https://HOTEL_DOMAIN/PARENT_SLUG/CURRENT_SLUG/"
    }
  ]
}
```

> For top-level pages (e.g. `/rooms`), use only positions 1 and 2. For room singles (`/rooms/{slug}`), use all three.

---

## 25. WordPress implementation notes

### Delivery method

All JSON-LD blocks should be injected via `wp_head` hook, not hardcoded in templates. Recommended approaches in order of preference:

1. **Portico Hotel Manager plugin** — extend the plugin to output schema from `pw_property` and CPT meta fields automatically per page template. This is the cleanest approach for dynamic values.
2. **RankMath SEO** — use RankMath's schema module. Supports `Hotel`, `LocalBusiness`, `FAQPage`, `Article`, `Event` natively. Extend via custom schema blocks for room-level MTE.
3. **Custom `functions.php` hook** — for schemas that RankMath doesn't cover (MeetingRoom MTE, Offer bundles), output via `add_action('wp_head', 'pw_output_room_schema')`.

### Multiple schemas on one page

When a page needs more than one schema type, output them as separate `<script>` blocks — do not merge into a single block.

```php
// functions.php pattern
add_action('wp_head', function() {
    if (is_singular('pw_room_type')) {
        echo '<script type="application/ld+json">';
        echo json_encode(pw_get_room_schema(get_the_ID()), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        echo '</script>';

        echo '<script type="application/ld+json">';
        echo json_encode(pw_get_breadcrumb_schema(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        echo '</script>';
    }
});
```

### Dynamic field mapping

| Schema field | Portico plugin source |
|---|---|
| `name` (hotel) | `get_post_meta($id, '_pw_property_name', true)` |
| `address` | `_pw_address_line1`, `_pw_address_city`, `_pw_address_state`, `_pw_address_pin` |
| `geo` | `_pw_lat`, `_pw_lng` |
| `starRating` | `_pw_star_rating` |
| `checkinTime` | `_pw_checkin_time` |
| `checkoutTime` | `_pw_checkout_time` |
| `numberOfRooms` | `_pw_total_rooms` |
| `amenityFeature` | `_pw_amenities` (repeatable group) |
| Room `name` | `get_the_title()` on `pw_room_type` post |
| Room `bed` | `_pw_bed_type`, `_pw_bed_count` |
| Room `occupancy` | `_pw_max_occupancy` |
| Room `floorSize` | `_pw_room_size_sqm` |
| Room `price` | `_pw_rack_rate` |
| Room `amenityFeature` | `_pw_features` → resolved `pw_feature` posts |
| Event `startDate` | `_pw_event_start` (store as ISO 8601) |
| Offer `validThrough` | `_pw_offer_end_date` |

### Testing

Use Google's Rich Results Test after deployment: `https://search.google.com/test/rich-results`

Priority pages to test first: `/rooms/{slug}`, `/faq`, `/events/{slug}`.

---

## 26. Placeholder key

Replace all `CAPS_SNAKE_CASE` values before deployment.

| Placeholder | Description | Example |
|---|---|---|
| `HOTEL_DOMAIN` | Domain without trailing slash | `thegrandcalicut.com` |
| `HOTEL_NAME` | Full legal/brand name | `The Grand Calicut` |
| `HOTEL_SHORT_NAME` | Abbreviated name | `Grand Calicut` |
| `HOTEL_META_DESCRIPTION` | 150-char hotel description | — |
| `LOGO_FILE` | Logo filename in `/uploads/` | `logo-grand-calicut.png` |
| `PHONE_NUMBER` | With STD code, no spaces | `4952711234` |
| `STREET_ADDRESS` | Full street address | `SM Street, Kozhikode` |
| `CITY` | City name | `Kozhikode` |
| `STATE` | State name | `Kerala` |
| `PIN_CODE` | 6-digit PIN | `673001` |
| `LAT` / `LNG` | Decimal coordinates | `11.2588`, `75.7804` |
| `STAR_COUNT` | 1–5 | `4` |
| `RATING_AUTHORITY` | Who awarded stars | `Ministry of Tourism India` |
| `AGGREGATE_SCORE` | Avg score, 1 decimal | `8.7` |
| `REVIEW_COUNT` | Integer | `432` |
| `PRICE_MIN` / `PRICE_MAX` | INR rack rates | `3500` / `12000` |
| `TOTAL_ROOM_COUNT` | Integer | `72` |
| `ROOM_SLUG` | CPT post slug | `deluxe-king-room` |
| `ROOM_TYPE_NAME` | Display name | `Deluxe King Room` |
| `ROOM_DESCRIPTION` | 150-char room description | — |
| `BED_TYPE` | Bed type string | `King Bed` |
| `BED_COUNT` | Integer | `1` |
| `MAX_OCCUPANCY` | Integer | `2` |
| `ROOM_SIZE_SQM` | Integer | `32` |
| `RACK_RATE` | Integer INR | `5500` |
| `MIN_PRICE` / `MAX_PRICE` | Seasonal range | `3800` / `7500` |
| `VIEW_TYPE` | Room view feature | `Ocean View` |
| `BB_RATE` | B&B package rate | `6500` |
| `VENUE_SIZE_SQM` | Integer | `120` |
| `MAX_CAPACITY` | Integer | `150` |
| `VENUE_FULLDAY_RATE` | Integer INR | `45000` |
| `EXPERIENCE_PRICE` | Integer INR | `1200` |
| `OFFER_PRICE` | Integer INR | `4500` |
| `OFFER_START_DATE` | ISO date | `2025-10-01` |
| `OFFER_END_DATE` | ISO date | `2026-02-28` |
| `EVENT_TICKET_PRICE` | Integer INR (0 if free) | `500` |
| `POST_TITLE` | Blog post H1 | — |
| `WORD_COUNT` | Integer | `1400` |
| `DIRECT_BENEFIT_1/2/3` | Direct booking USPs | `Early check-in` |
| `TRIPADVISOR_PROFILE_URL` | TA URL path | `/Hotel_Review-g297623-...` |
| `FACEBOOK_PAGE` | FB page slug | `thegrandcalicut` |
| `INSTAGRAM_HANDLE` | IG username | `thegrandcalicut` |

---

*Schema.org version: v29.4 (December 2025). Validate at https://search.google.com/test/rich-results before going live.*
