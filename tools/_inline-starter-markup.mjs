/**
 * Writes includes/_pw_starter_match_fragment.txt for tools/_merge-page-installer-markup.mjs.
 * Requires includes/page-installer-starter-fragments.php (from _extract-gb-starter + _build-starter-php).
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname( fileURLToPath( import.meta.url ) );
const root = path.join( __dirname, '..' );
const src = fs.readFileSync( path.join( root, 'includes/page-installer-starter-fragments.php' ), 'utf8' );

const order = [
	'pw_room_type',
	'pw_restaurant',
	'pw_spa',
	'pw_meeting_room',
	'pw_experience',
	'pw_nearby',
	'pw_event',
	'pw_offer',
];
const map = {};
const re = /'(pw_[a-z_]+)' => <<<'([A-Z0-9_]+)'\r?\n([\s\S]*?)\r?\n\2,/g;
let m;
while ( ( m = re.exec( src ) ) ) {
	map[ m[ 1 ] ] = m[ 3 ];
}
for ( const k of order ) {
	if ( ! map[ k ] ) {
		throw new Error( 'missing ' + k );
	}
}

let nb = map.pw_nearby;
nb = nb.replace( /repeat\(7, minmax\(5\.5rem, 1fr\)\)/g, 'repeat(5, minmax(5.5rem, 1fr))' );
nb = nb.replace( /grid-template-columns:repeat\(7,minmax\(5\.5rem,1fr\)\)/g, 'grid-template-columns:repeat(5,minmax(5.5rem,1fr))' );
nb = nb.replace(
	/\n<!-- wp:generateblocks\/text \{"uniqueId":"ch_nrrowc4"[\s\S]*?<!-- \/wp:generateblocks\/text -->\n<!-- wp:generateblocks\/text \{"uniqueId":"ch_nrrowc5"[\s\S]*?<!-- \/wp:generateblocks\/text -->\n/,
	'\n'
);
map.pw_nearby = nb;

const renames = [
	[ 'pw_room_type', { ch_rtq: 'rmq', lop_rt: 'rm-loop', lit_rt: 'rm-item', ch_rtnr: 'rm-nr' } ],
	[ 'pw_restaurant', { ch_rsq: 'rstq', lop_rs: 'rst-loop', lit_rs: 'rst-item', ch_rsnr: 'rst-nr' } ],
	[ 'pw_spa', { ch_spq: 'spaq', lop_sp: 'spa-loop', lit_sp: 'spa-item', ch_spnr: 'spa-nr' } ],
	[ 'pw_meeting_room', { ch_mrq: 'mtq', lop_mr: 'mt-loop', lit_mr: 'mt-item', ch_mrnr: 'mt-nr' } ],
	[ 'pw_experience', { ch_exq: 'exq', lop_ex: 'ex-loop', lit_ex: 'ex-item', ch_exnr: 'ex-nr' } ],
	[ 'pw_nearby', { ch_nrq: 'nbq', lop_nr: 'nb-loop', lit_nr: 'nb-item', ch_nrnr: 'nb-nr' } ],
	[ 'pw_event', { ch_evq: 'evq', lop_ev: 'ev-loop', lit_ev: 'ev-item', ch_evnr: 'ev-nr' } ],
	[ 'pw_offer', { ch_ofq: 'ofq', lop_of: 'of-loop', lit_of: 'of-item', ch_ofnr: 'of-nr' } ],
];
for ( const [ cpt, dict ] of renames ) {
	let s = map[ cpt ];
	for ( const [ from, to ] of Object.entries( dict ) ) {
		s = s.split( from ).join( to );
	}
	map[ cpt ] = s;
}

const tag = ( k ) => 'PW_ST_' + k.replace( /^pw_/, '' ).toUpperCase();
const arms = order.map(
	( k ) => `\t\t'${ k }' => <<<'${ tag( k ) }'\n${ map[ k ] }\n${ tag( k ) },`
);
const out =
	'\n\treturn match ( $cpt ) {\n' + arms.join( '\n' ) + "\n\t\tdefault => '',\n\t};";

fs.writeFileSync( path.join( root, 'includes/_pw_starter_match_fragment.txt' ), out );
console.log( 'bytes', out.length );
