// Deterministic, dependency-free build. WordPress provides runtime packages.
const fs = require('fs'), crypto = require('crypto');
function build() {
    const source = fs.readFileSync(__dirname+'/src/editor.js','utf8');
    fs.writeFileSync(__dirname+'/build/index.js',source);
    const version = crypto.createHash('sha256').update(source).digest('hex').slice(0,20);
    fs.writeFileSync(__dirname+'/build/index.asset.php',"<?php return array('dependencies'=>array('wp-block-editor','wp-blocks','wp-components','wp-data','wp-element','wp-i18n','oum_editor_map','oum_editor_filters'),'version'=>'"+version+"');\n");
    require('./build-translations.js').generate();
}
build();
if (process.argv.includes('--watch')) fs.watch(__dirname+'/src/editor.js', build);
