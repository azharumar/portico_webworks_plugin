import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const root = path.join( path.dirname( fileURLToPath( import.meta.url ) ), '..' );
const s = fs.readFileSync( path.join( root, 'includes/page-installer.php' ), 'utf8' );
const i = s.indexOf( 'function pw_get_section_starter_markup' );
const j = s.indexOf( 'function pw_installer_section_plural_bases_changed' );
const fn = s.slice( i, j );
const ids = [ ...fn.matchAll( /"uniqueId":"([^"]+)"/g ) ].map( ( m ) => m[ 1 ] );
const c = {};
ids.forEach( ( id ) => {
	c[ id ] = ( c[ id ] || 0 ) + 1;
} );
const dups = Object.entries( c ).filter( ( [ , n ] ) => n > 1 );
console.log( 'uniqueId values:', Object.keys( c ).length, 'duplicates:', dups.length );
if ( dups.length ) {
	console.log( dups );
	process.exit( 1 );
}
