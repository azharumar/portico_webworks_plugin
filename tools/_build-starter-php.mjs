import fs from 'fs';
const j = JSON.parse(fs.readFileSync('includes/_pw_starter_markup.json', 'utf8'));
const map = {
	room: 'pw_room_type',
	rst: 'pw_restaurant',
	spa: 'pw_spa',
	mr: 'pw_meeting_room',
	ex: 'pw_experience',
	nr: 'pw_nearby',
	ev: 'pw_event',
	of: 'pw_offer',
};
let out =
	"\n/**\n * GenerateBlocks starter fragments (from gb-pro-markup-samples.html via tools/_extract-gb-starter.mjs).\n */\nfunction pw_gb_starter_fragment_strings(): array {\n\treturn [\n";
for (const [key, cpt] of Object.entries(map)) {
	const tag = `PWGB_${key.toUpperCase()}_END`;
	const body = j[key];
	if (body.includes('\n' + tag + '\n') || body.endsWith(tag)) {
		throw new Error('delimiter collision ' + tag);
	}
	out += `\t\t'${cpt}' => <<<'${tag}'\n`;
	out += body;
	out += `\n${tag},\n`;
}
out += '\t];\n}\n';
fs.writeFileSync('includes/page-installer-starter-fragments.php', '<?php\nif ( ! defined( \'ABSPATH\' ) ) {\n\texit;\n}\n' + out, 'utf8');
console.log('wrote includes/page-installer-starter-fragments.php');
