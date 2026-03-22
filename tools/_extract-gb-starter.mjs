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
// Append sales rows inside meeting loop-item before closing loop-item (after ch_mrrow element)
const salesBlock = `<!-- wp:generateblocks/element {"uniqueId":"mt-srw0","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-mt-srw0{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-mt-srw0{grid-template-columns:1fr}}","className":"gb-el gb-el-mt-srw0"} -->
<div class="gb-element-mt-srw0 gb-el gb-el-mt-srw0"><!-- wp:generateblocks/text {"uniqueId":"mt-slk0","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-mt-slk0{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-mt-slk0"} -->
<div class="gb-text gb-text-mt-slk0 gb-t-mt-slk0">Sales email</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"mt-svk0","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-mt-svk0{margin-bottom:0px;font-size:14px}","className":"gb-t-mt-svk0"} -->
<div class="gb-text gb-text-mt-svk0 gb-t-mt-svk0">{{post_meta key:_pw_sales_email}}</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"mt-srw1","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-mt-srw1{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-mt-srw1{grid-template-columns:1fr}}","className":"gb-el gb-el-mt-srw1"} -->
<div class="gb-element-mt-srw1 gb-el gb-el-mt-srw1"><!-- wp:generateblocks/text {"uniqueId":"mt-slk1","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-mt-slk1{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-mt-slk1"} -->
<div class="gb-text gb-text-mt-slk1 gb-t-mt-slk1">Sales phone</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"mt-svk1","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-mt-svk1{margin-bottom:0px;font-size:14px}","className":"gb-t-mt-svk1"} -->
<div class="gb-text gb-text-mt-svk1 gb-t-mt-svk1">{{post_meta key:_pw_sales_phone}}</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
`;
const mrNeedle = '</div>\n<!-- /wp:generateblocks/element -->\n</div>\n<!-- /wp:generateblocks/loop-item -->';
const mrIdx = out.mr.lastIndexOf(mrNeedle);
if (mrIdx === -1) throw new Error('meeting needle');
out.mr = out.mr.slice(0, mrIdx) + salesBlock + out.mr.slice(mrIdx);

fs.writeFileSync(
	'includes/_pw_starter_markup.json',
	JSON.stringify(out, null, 0),
	'utf8'
);
console.log('wrote includes/_pw_starter_markup.json', Object.keys(out).map((k) => k + ':' + out[k].length));
