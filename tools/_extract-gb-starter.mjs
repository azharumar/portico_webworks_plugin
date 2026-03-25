import fs from 'fs';
const f = fs.readFileSync('gb-pro-markup-samples.html', 'utf8');
const markers = {
	room: ['<!-- wp:generateblocks/query {"uniqueId":"ch_rtq"', '<!-- wp:generateblocks/element {"uniqueId":"ch_rs"'],
	rst: ['<!-- wp:generateblocks/query {"uniqueId":"ch_rsq"', '<!-- wp:generateblocks/element {"uniqueId":"ch_sp"'],
	spa: ['<!-- wp:generateblocks/query {"uniqueId":"ch_spq"', '<!-- wp:generateblocks/element {"uniqueId":"ch_mr"'],
	mr: ['<!-- wp:generateblocks/query {"uniqueId":"ch_mrq"', '<!-- wp:generateblocks/element {"uniqueId":"ch_am"'],
	ex: ['<!-- wp:generateblocks/query {"uniqueId":"ch_exq"', '<!-- wp:generateblocks/element {"uniqueId":"ch_nr"'],
	nr: ['<!-- wp:generateblocks/query {"uniqueId":"ch_nrq"', '<!-- wp:generateblocks/element {"uniqueId":"ch_ev"'],
	ev: ['<!-- wp:generateblocks/query {"uniqueId":"ch_evq"', '<!-- wp:generateblocks/element {"uniqueId":"ch_of"'],
	of: ['<!-- wp:generateblocks/query {"uniqueId":"ch_ofq"', '<!-- wp:generateblocks/element {"uniqueId":"ch_pl"'],
};
const out = {};
for (const [k, [s, e]] of Object.entries(markers)) {
	const a = f.indexOf(s);
	const b = f.indexOf(e, a);
	if (a < 0 || b < 0) throw new Error('marker ' + k);
	out[k] = f.slice(a, b).trimEnd();
}
// Strip venue row from event (evrw4 block)
out.ev = out.ev.replace(
	/<!-- wp:generateblocks\/element \{"uniqueId":"evrw4"[\s\S]*?<!-- \/wp:generateblocks\/element -->\n/,
	''
);

fs.writeFileSync(
	'includes/_pw_starter_markup.json',
	JSON.stringify(out, null, 0),
	'utf8'
);
console.log('wrote includes/_pw_starter_markup.json', Object.keys(out).map((k) => k + ':' + out[k].length));
