// Generate bundled WordPress script translations from the existing PO catalogs.
const fs = require('fs');
const path = require('path');
const domain = 'open-user-map';
const scripts = [
    {handle: 'open-user-map-map-editor-script', source: 'blocks/build/index.js'},
    {handle: 'oum_editor_filters', source: 'src/js/editor-filters.js'}
];

function parsePO(source) {
    const entries = [];
    let entry = {}, field = null;
    function finish() {
        if (entry.msgid !== undefined && !entry.fuzzy) entries.push(entry);
        entry = {}; field = null;
    }
    source.replace(/^\uFEFF/, '').split(/\r?\n/).forEach((line, index) => {
        if (!line.trim()) {finish(); return;}
        if (line.startsWith('#~')) return;
        if (line.startsWith('#,')) {if (/\bfuzzy\b/.test(line)) entry.fuzzy = true; return;}
        if (line.startsWith('#')) return;
        const match = line.match(/^(msgctxt|msgid_plural|msgid|msgstr(?:\[\d+\])?)\s+(".*")$/);
        if (match) {
            field = match[1];
            entry[field] = JSON.parse(match[2]);
        } else if (line.startsWith('"') && field) {
            entry[field] += JSON.parse(line);
        } else {
            throw new Error('Unsupported PO syntax at line '+(index+1));
        }
    });
    finish();
    return entries;
}

function sourceStrings(source) {
    const values = new Set();
    // Include dynamic labels as well as direct __() calls; intersect with the PO below.
    const quoted = /(['"])((?:\\.|(?!\1)[^\\\r\n])*)\1/g;
    for (const match of source.matchAll(quoted)) {
        values.add(match[2].replace(/\\(u[\da-f]{4}|x[\da-f]{2}|.)/gi, (_, escape) => {
            if (/^[ux]/i.test(escape) && escape.length > 1) return String.fromCharCode(parseInt(escape.slice(1),16));
            return ({n:'\n',r:'\r',t:'\t',b:'\b',f:'\f',v:'\v','0':'\0'})[escape] || escape;
        }));
    }
    return values;
}

function generate(root = path.resolve(__dirname, '..')) {
    const directory = path.join(root, 'languages');
    const result = [];
    for (const name of fs.readdirSync(directory).filter(name => /^open-user-map-.+\.po$/.test(name)).sort()) {
        const locale = name.slice(domain.length+1,-3);
        const entries = parsePO(fs.readFileSync(path.join(directory,name),'utf8'));
        const header = entries.find(entry => entry.msgid === '');
        if (!header) throw new Error('Missing PO header: '+name);
        const metadata = Object.fromEntries(header.msgstr.split('\n').filter(line => line.includes(':')).map(line => {
            const colon = line.indexOf(':'); return [line.slice(0,colon),line.slice(colon+1).trim()];
        }));
        if (!metadata['Plural-Forms']) throw new Error('Missing plural rules: '+name);
        for (const script of scripts) {
            const strings = sourceStrings(fs.readFileSync(path.join(root,script.source),'utf8'));
            const messages = {'': {domain,lang:locale,'plural-forms':metadata['Plural-Forms']}};
            for (const entry of entries) {
                if (!entry.msgid || !strings.has(entry.msgid)) continue;
                const translations = entry.msgid_plural === undefined ? [entry.msgstr || ''] : Object.keys(entry)
                    .filter(key => /^msgstr\[\d+\]$/.test(key)).sort((a,b) => parseInt(a.slice(7))-parseInt(b.slice(7))).map(key => entry[key]);
                if (!translations.length || translations.some(value => !value)) continue;
                messages[(entry.msgctxt ? entry.msgctxt+'\u0004' : '')+entry.msgid] = translations;
            }
            const catalog = {'translation-revision-date':metadata['PO-Revision-Date'] || '',generator:'Open User Map PO to JSON',domain:'messages',source:script.source,locale_data:{messages}};
            const filename = domain+'-'+locale+'-'+script.handle+'.json';
            fs.writeFileSync(path.join(directory,filename),JSON.stringify(catalog,null,2)+'\n');
            result.push({file:filename,messages:Object.keys(messages).length-1});
        }
    }
    return result;
}

if (require.main === module) console.log(JSON.stringify(generate(),null,2));
module.exports = {generate,parsePO,sourceStrings};
