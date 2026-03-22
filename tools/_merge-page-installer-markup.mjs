/**
 * Inlines _pw_starter_match_fragment.txt into includes/page-installer.php (removes fragments require).
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname( fileURLToPath( import.meta.url ) );
const root = path.join( __dirname, '..' );
const main = path.join( root, 'includes/page-installer.php' );
const fragPath = path.join( root, 'includes/_pw_starter_match_fragment.txt' );

let s = fs.readFileSync( main, 'utf8' );
s = s.replace( /\r?\nrequire_once __DIR__ \. '\/page-installer-starter-fragments\.php';\r?\n\r?\n/, '\n\n' );

const frag = fs.readFileSync( fragPath, 'utf8' ).replace( /^\r?\n\treturn match/, '\n\treturn match' );

const newBlock = `/**
 * GenerateBlocks starter post_content for section CPT listing pages (insert only).
 * Derived from gb-pro-markup-samples.html. Room type: pw_bed_type / pw_view_type omitted (no verified GB Pro term-list tag in loops).
 *
 * @param string $cpt Section CPT or empty; pw_property returns empty (designer-owned listing).
 */
function pw_get_section_starter_markup( string $cpt ): string {
	if ( $cpt === '' || $cpt === 'pw_property' ) {
		// Property listing page layout varies too much per project. The designer builds this page. No starter markup is inserted.
		return '';
	}
${ frag }
}
`;

const startMark = '/**\n * GenerateBlocks starter post_content for section CPT listing pages (insert only).';
const i = s.indexOf( startMark );
if ( i === -1 ) {
	throw new Error( 'start marker not found' );
}
const endMark = "\n}\n\n/**\n * True if any section plural base";
const j = s.indexOf( endMark, i );
if ( j === -1 ) {
	throw new Error( 'end marker not found' );
}
const out = s.slice( 0, i ) + newBlock + s.slice( j + '\n}\n\n'.length );
fs.writeFileSync( main, out );
console.log( 'merged', main );
