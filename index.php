<?php
$imagesDir = __DIR__ . '/images';
if (!is_dir($imagesDir)) mkdir($imagesDir, 0755, true);
?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Skrivövning</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Patrick+Hand&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body { background: #6b6b6b; font-family: system-ui, -apple-system, sans-serif; }

    /* ── Always-visible landscape sheet in background ── */
    #sheet-bg {
      min-height: 100vh;
      padding: 24px;
      display: flex;
      justify-content: center;
      align-items: flex-start;
    }
    .sheet {
      width: 277mm;
      min-height: 190mm;
      background: white;
      padding: 10mm 12mm;
      box-shadow: 0 4px 24px rgba(0,0,0,.55);
      flex-shrink: 0;
    }
    .sheet-placeholder {
      color: #bbb;
      text-align: center;
      padding: 30mm 0;
      font-size: 14px;
    }

    /* ── Input panel ── */
    #input-panel {
      position: fixed;
      top: 0; left: 0;
      width: 460px;
      height: 100vh;
      overflow-y: auto;
      background: white;
      box-shadow: 4px 0 24px rgba(0,0,0,.35);
      z-index: 10;
      padding: 22px 24px 32px;
    }

    h1 { font-size: 1.3rem; color: #1a1a1a; margin-bottom: 4px; }
    .subtitle { font-size: .8rem; color: #666; margin-bottom: 14px; line-height: 1.45; }

    /* ── Segmented controls (mode + image sub-mode) ── */
    .seg-ctrl {
      display: flex;
      margin-bottom: 16px;
      border: 2px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
    }
    .seg-btn {
      flex: 1;
      padding: 8px 10px;
      background: none;
      border: none;
      border-right: 1px solid #ddd;
      font-size: .875rem;
      font-weight: 500;
      color: #666;
      cursor: pointer;
      transition: background .15s, color .15s;
    }
    .seg-btn:last-child { border-right: none; }
    .seg-btn.active {
      background: #4f8ef7;
      color: white;
    }
    .seg-ctrl.green .seg-btn.active {
      background: #2ca85a;
    }

    /* ── Word grid ── */
    .word-grid {
      display: grid; grid-template-columns: 1fr 1fr; gap: 7px;
      margin-bottom: 16px;
    }
    .word-grid input {
      padding: 8px 11px;
      border: 2px solid #ddd; border-radius: 7px;
      font-size: .95rem; transition: border-color .15s;
    }
    .word-grid input:focus { outline: none; border-color: #4f8ef7; }

    /* ── Image mode section ── */
    #image-mode-section { margin-bottom: 14px; }

    /* Word image pickers (Färdig bild) */
    #word-image-pickers { display: grid; gap: 6px; margin-bottom: 10px; }
    .word-image-row {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 5px 8px;
      background: #fafafa;
      border: 1px solid #eee;
      border-radius: 7px;
    }
    .word-label { flex: 1; font-size: .88rem; font-weight: 600; color: #333; }
    .word-thumb-small {
      width: 34px; height: 34px;
      object-fit: cover;
      border-radius: 4px;
      border: 2px solid #ddd;
      cursor: pointer;
    }
    .btn-pick {
      padding: 4px 10px;
      background: #f0f0f0;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: .78rem;
      cursor: pointer;
      white-space: nowrap;
      transition: background .15s;
    }
    .btn-pick:hover { background: #e4e4e4; }
    .no-words-hint { font-size: .78rem; color: #aaa; font-style: italic; padding: 4px 0; }

    /* Generate section (Skapa ny bild) */
    #generate-section {
      display: none;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 10px;
    }
    .btn-generate {
      padding: 8px 16px;
      background: #2ca85a;
      color: white;
      border: none;
      border-radius: 7px;
      font-size: .875rem;
      font-weight: 600;
      cursor: pointer;
      transition: background .15s;
      white-space: nowrap;
    }
    .btn-generate:hover:not(:disabled) { background: #24914d; }
    .btn-generate:disabled { background: #aaa; cursor: not-allowed; }
    #generate-status { font-size: .8rem; color: #666; }

    /* ── Settings ── */
    details {
      border: 1px solid #e5e5e5; border-radius: 8px;
      margin-bottom: 16px; overflow: hidden;
    }
    summary {
      padding: 9px 12px; cursor: pointer; list-style: none;
      font-size: .82rem; font-weight: 600; color: #444;
      background: #fafafa; user-select: none;
    }
    summary::before { content: '▸ '; }
    details[open] summary::before { content: '▾ '; }
    summary::-webkit-details-marker { display: none; }

    .cfg-body { padding: 10px 12px; display: grid; gap: 9px; }
    .cfg-row {
      display: grid;
      grid-template-columns: 1fr 120px 42px;
      align-items: center; gap: 6px;
    }
    .cfg-row label { font-size: .77rem; color: #555; line-height: 1.35; }
    .cfg-row input[type=range] { accent-color: #4f8ef7; width: 100%; }
    .cfg-row .vl {
      font-size: .75rem; color: #888;
      text-align: right; font-variant-numeric: tabular-nums;
    }
    .cfg-check {
      display: flex; align-items: center; gap: 7px;
      font-size: .77rem; color: #555; cursor: pointer;
      padding-top: 2px;
    }
    .cfg-check input[type=checkbox] {
      width: 14px; height: 14px;
      cursor: pointer; accent-color: #4f8ef7; flex-shrink: 0;
    }

    .btn-go {
      width: 100%; padding: 11px;
      background: #4f8ef7; color: #fff;
      border: none; border-radius: 8px;
      font-size: .95rem; font-weight: 600; cursor: pointer;
      transition: background .15s;
    }
    .btn-go:hover { background: #3a76e0; }

    /* ── Print toolbar ── */
    #toolbar {
      display: none;
      position: fixed;
      top: 0; left: 0; right: 0; z-index: 10;
      padding: 8px 14px; background: #e8eaed;
      border-bottom: 1px solid #ccc;
      align-items: center; gap: 8px;
    }
    #toolbar button {
      padding: 6px 14px; border: none; border-radius: 6px;
      font-size: .875rem; font-weight: 500; cursor: pointer;
    }
    .btn-back  { background: #d5d5d5; color: #333; }
    .btn-back:hover  { background: #c0c0c0; }
    .btn-print { background: #4f8ef7; color: #fff; }
    .btn-print:hover { background: #3a76e0; }
    .toolbar-hint { font-size: .78rem; color: #777; margin-left: 4px; }

    /* ── Word rows on sheet ── */
    .wr { display: flex; width: 100%; position: relative; }
    .wr + .wr { margin-top: 2.5mm; }

    .ex-cell { position: relative; flex-shrink: 0; border-right: .4mm solid #bbb; }
    .ex-word {
      font-family: 'Patrick Hand', cursive;
      position: absolute; left: 2mm;
      white-space: nowrap; line-height: 1;
    }
    .ex-img {
      position: absolute;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      max-height: 90%; max-width: 90%;
      object-fit: contain;
    }
    .ex-placeholder {
      position: absolute; inset: 0;
      display: flex; align-items: center; justify-content: center;
      color: #ccc; font-size: 8px; font-style: italic;
      text-align: center; padding: 2mm;
    }

    .prac-area { flex: 1; display: flex; }
    .prac-cell { flex: 1; position: relative; overflow: hidden; }
    .prac-cell + .prac-cell { border-left: .25mm solid #ddd; }

    .gl { position: absolute; left: 0; right: 0; height: 0; pointer-events: none; }
    .gl-a { border-top: .3mm dotted #b0b0b0; }
    .gl-x { border-top: .3mm dotted #b0b0b0; }
    .gl-b { border-top: .45mm solid  #999;   }
    .gl-d { border-top: .3mm dotted #b0b0b0; }

    /* ── Image picker dialog ── */
    .dialog-overlay {
      position: fixed; inset: 0;
      background: rgba(0,0,0,.6);
      z-index: 200;
      display: flex; align-items: center; justify-content: center;
    }
    .dialog-overlay.hidden { display: none; }
    .dialog-box {
      background: white;
      border-radius: 12px;
      padding: 20px 22px;
      width: 580px; max-width: 95vw;
      max-height: 82vh;
      display: flex; flex-direction: column; gap: 14px;
      box-shadow: 0 8px 40px rgba(0,0,0,.35);
    }
    .dialog-header {
      display: flex; justify-content: space-between; align-items: center;
    }
    .dialog-header h3 { font-size: 1rem; font-weight: 600; }
    .btn-close {
      background: none; border: none;
      font-size: 1.5rem; cursor: pointer;
      color: #888; line-height: 1; padding: 0 4px;
    }
    .btn-close:hover { color: #333; }
    .dialog-upload { display: flex; align-items: center; gap: 10px; }
    .btn-upload-label {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 7px 13px;
      background: #f0f0f0; border: 1px solid #ddd; border-radius: 7px;
      cursor: pointer; font-size: .82rem; font-weight: 500;
      transition: background .15s;
    }
    .btn-upload-label:hover { background: #e4e4e4; }
    .upload-hint { font-size: .75rem; color: #aaa; }
    .image-picker-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
      gap: 10px;
      overflow-y: auto; flex: 1;
      padding-right: 4px;
      min-height: 120px;
    }
    .image-picker-thumb {
      cursor: pointer;
      border: 3px solid transparent;
      border-radius: 8px;
      overflow: hidden;
      display: flex; flex-direction: column;
      transition: border-color .15s, transform .1s;
    }
    .image-picker-thumb:hover { border-color: #4f8ef7; transform: scale(1.03); }
    .image-picker-thumb img {
      width: 100%; aspect-ratio: 1;
      object-fit: cover; display: block;
    }
    .thumb-label {
      font-size: .63rem; color: #888;
      padding: 2px 4px; background: #fafafa;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .picker-msg { font-size: .82rem; color: #aaa; text-align: center; padding: 24px 0; }

    /* Upload name field */
    .upload-name-input {
      flex: 1; min-width: 0;
      padding: 6px 10px;
      border: 1px solid #ddd; border-radius: 7px;
      font-size: .82rem;
    }
    .upload-name-input:focus { outline: none; border-color: #4f8ef7; }

    /* ── Context menu ── */
    .ctx-menu {
      position: fixed;
      background: white;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      box-shadow: 0 4px 20px rgba(0,0,0,.18);
      z-index: 300;
      min-width: 150px;
      overflow: hidden;
      padding: 4px 0;
    }
    .ctx-menu.hidden { display: none; }
    .ctx-menu button {
      display: block; width: 100%;
      padding: 9px 16px;
      text-align: left; background: none; border: none;
      font-size: .875rem; cursor: pointer; color: #333;
      transition: background .1s;
    }
    .ctx-menu button:hover { background: #f5f5f5; }
    .ctx-menu button.danger { color: #d63031; }
    .ctx-menu button.danger:hover { background: #fff0f0; }
    .ctx-menu-sep { height: 1px; background: #eee; margin: 3px 0; }

    /* ── Panel drag handle (mobile only) ── */
    .panel-handle { display: none; }
    .panel-handle-label { font-size: .72rem; color: #999; font-weight: 500; letter-spacing: .02em; }

    /* ── Mobile: bottom-sheet panel + full-screen sheet ── */
    @media (max-width: 767px) {
      #sheet-bg {
        position: fixed; inset: 0;
        padding: 0; min-height: 0;
        display: block; overflow: hidden;
      }
      .sheet {
        transform-origin: top left;
        box-shadow: none; min-height: 0;
      }
      #input-panel {
        top: auto; bottom: 0; left: 0; right: 0;
        width: 100%; height: 85vh;
        border-radius: 16px 16px 0 0;
        box-shadow: 0 -4px 24px rgba(0,0,0,.22);
        transform: translateY(calc(100% - 60px));
        transition: transform .3s cubic-bezier(.32,0,.67,0);
        padding: 0 20px 40px;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
      }
      .panel-handle {
        display: flex; flex-direction: column;
        align-items: center; gap: 5px;
        padding: 10px 0 8px;
        cursor: pointer; user-select: none; -webkit-user-select: none;
        position: sticky; top: 0;
        background: white; border-radius: 16px 16px 0 0; z-index: 1;
      }
      .panel-handle::before {
        content: ''; display: block;
        width: 36px; height: 4px;
        background: #d0d0d0; border-radius: 2px;
      }
      #toolbar { z-index: 30; }
    }

    /* ── Print ── */
    @page { size: A4 portrait; margin: 10mm; }

    @media print {
      body { background: white; }
      #input-panel, #toolbar { display: none !important; }
      #sheet-bg { position: static; padding: 0; min-height: unset; display: block; }
      /* Rotate landscape sheet (-90°) to fill portrait A4 (190×277 mm printable) */
      .sheet {
        box-shadow: none; padding: 0; margin: 0;
        position: fixed; top: 0; left: 0;
        width: 277mm; height: 190mm;
        transform: translateY(277mm) rotate(-90deg);
        transform-origin: 0 0;
      }
    }
  </style>
</head>
<body>

<!-- Always-visible landscape sheet preview -->
<div id="sheet-bg">
  <div class="sheet" id="sheet">
    <p class="sheet-placeholder">Skriv in ord i panelen till vänster för att se förhandsgranskning</p>
  </div>
</div>

<!-- Input panel overlay -->
<div id="input-panel">
  <div class="panel-handle" onclick="togglePanel()">
    <span class="panel-handle-label">Redigera</span>
  </div>
  <h1>Skrivövning</h1>
  <p class="subtitle">Skriv in upp till sex ord. Arket uppdateras direkt bakom panelen.</p>

  <!-- Mode selector: Text / Bild -->
  <div class="seg-ctrl" id="mode-ctrl">
    <button class="seg-btn active" id="seg-text"  onclick="setMode('text')">Text</button>
    <button class="seg-btn"        id="seg-image" onclick="setMode('image')">Bild</button>
  </div>

  <!-- Word inputs -->
  <div class="word-grid">
    <input type="text" id="w1" placeholder="Ord 1" autocomplete="off" spellcheck="false">
    <input type="text" id="w2" placeholder="Ord 2" autocomplete="off" spellcheck="false">
    <input type="text" id="w3" placeholder="Ord 3" autocomplete="off" spellcheck="false">
    <input type="text" id="w4" placeholder="Ord 4" autocomplete="off" spellcheck="false">
    <input type="text" id="w5" placeholder="Ord 5" autocomplete="off" spellcheck="false">
    <input type="text" id="w6" placeholder="Ord 6" autocomplete="off" spellcheck="false">
  </div>

  <!-- Image mode section (hidden until Bild is selected) -->
  <div id="image-mode-section" style="display:none">

    <!-- Sub-mode: Färdig bild / Skapa ny bild -->
    <div class="seg-ctrl green" id="imgmode-ctrl">
      <button class="seg-btn active" id="seg-existing"  onclick="setImageMode('existing')">Färdig bild</button>
      <button class="seg-btn"        id="seg-generate"  onclick="setImageMode('generate')">Skapa ny bild</button>
    </div>

    <!-- Färdig bild: per-word image pickers -->
    <div id="existing-section">
      <div id="word-image-pickers"></div>
    </div>

    <!-- Skapa ny bild: generate button -->
    <div id="generate-section">
      <button class="btn-generate" id="btn-generate" onclick="generateImages()">Generera bilder</button>
      <span id="generate-status"></span>
    </div>

  </div>

  <!-- Line settings -->
  <details open>
    <summary>Inställningar</summary>
    <div class="cfg-body">
      <div class="cfg-row">
        <label>Överkantslinje (versaler, långa)</label>
        <input type="range" id="c-as" min="5" max="45" value="12" step="1">
        <span class="vl" id="v-as">12 %</span>
      </div>
      <div class="cfg-row">
        <label>X-höjdslinje (a, o, u, e …)</label>
        <input type="range" id="c-xh" min="20" max="65" value="46" step="1">
        <span class="vl" id="v-xh">46 %</span>
      </div>
      <div class="cfg-row">
        <label>Baslinje — solid</label>
        <input type="range" id="c-bl" min="45" max="88" value="71" step="1">
        <span class="vl" id="v-bl">71 %</span>
      </div>
      <div class="cfg-row">
        <label>Underkantslinje (j, g, q, y …)</label>
        <input type="range" id="c-ds" min="65" max="99" value="88" step="1">
        <span class="vl" id="v-ds">88 %</span>
      </div>
      <div class="cfg-row">
        <label>Gråton (övningsbokstäver)</label>
        <input type="range" id="c-gray" min="130" max="240" value="200" step="5">
        <span class="vl" id="v-gray">#c8c8c8</span>
      </div>
      <label class="cfg-check">
        <input type="checkbox" id="c-leave-empty">
        Lämna en tom ruta till höger
      </label>
    </div>
  </details>

  <button class="btn-go" onclick="printMode()">Skriv ut / Spara PDF →</button>
</div>

<!-- Print-mode toolbar -->
<div id="toolbar">
  <button class="btn-back"  onclick="editMode()">← Tillbaka</button>
  <button class="btn-print" onclick="window.print()">Skriv ut / Spara PDF</button>
  <span class="toolbar-hint">Välj A4 stående · stäng av sidhuvuden i webbläsaren</span>
</div>

<!-- Image picker dialog -->
<div id="image-picker-dialog" class="dialog-overlay hidden"
     onclick="if(event.target===this) closeImagePicker()">
  <div class="dialog-box">
    <div class="dialog-header">
      <h3>Välj bild</h3>
      <button class="btn-close" onclick="closeImagePicker()">×</button>
    </div>
    <div class="dialog-upload">
      <input type="text" id="upload-name" class="upload-name-input"
             placeholder="Namn (valfritt)">
      <label class="btn-upload-label">
        + Ladda upp bild
        <input type="file" accept="image/jpeg,image/png,image/webp"
               style="display:none" onchange="handleUpload(event)">
      </label>
    </div>
    <div id="picker-grid" class="image-picker-grid">
      <p class="picker-msg">Laddar…</p>
    </div>
  </div>
</div>

<!-- Image context menu (right-click / long-press) -->
<div id="img-ctx-menu" class="ctx-menu hidden">
  <button onclick="ctxRename()">Byt namn</button>
  <div class="ctx-menu-sep"></div>
  <button class="danger" onclick="ctxDelete()">Ta bort</button>
</div>

<script>
  const MM2PX = 96 / 25.4;
  const RH = 30, EW = 52, NC = 5, FS = 50;
  const PRINT_W_MM = 253;

  // ── State ─────────────────────────────────────────────────────────
  let appMode   = 'text';     // 'text' | 'image'
  let imageMode = 'existing'; // 'existing' | 'generate'
  let wordImages = {};        // { slot: filename }
  let pickerSlot = null;

  // ── Mode switching ────────────────────────────────────────────────
  function setMode(mode) {
    appMode = mode;
    document.getElementById('seg-text').classList.toggle('active',  mode === 'text');
    document.getElementById('seg-image').classList.toggle('active', mode === 'image');
    document.getElementById('image-mode-section').style.display =
      mode === 'image' ? 'block' : 'none';
    if (mode === 'image') updateWordImagePickers();
    updateSheet();
  }

  function setImageMode(mode) {
    imageMode = mode;
    document.getElementById('seg-existing').classList.toggle('active',  mode === 'existing');
    document.getElementById('seg-generate').classList.toggle('active',  mode === 'generate');
    document.getElementById('existing-section').style.display =
      mode === 'existing' ? 'block' : 'none';
    document.getElementById('generate-section').style.display =
      mode === 'generate' ? 'flex' : 'none';
    if (mode === 'existing') updateWordImagePickers();
  }

  // ── Sliders ───────────────────────────────────────────────────────
  const pct = v => v + ' %';
  const hex3 = v => { const h = (+v).toString(16).padStart(2,'0'); return '#' + h + h + h; };
  const SLIDERS = [
    ['c-as', 'v-as', pct], ['c-xh', 'v-xh', pct], ['c-bl', 'v-bl', pct], ['c-ds', 'v-ds', pct],
    ['c-gray', 'v-gray', hex3],
  ];
  SLIDERS.forEach(([sid, vid, fmt]) => {
    const s = document.getElementById(sid);
    s.addEventListener('input', () => {
      document.getElementById(vid).textContent = fmt(s.value);
      updateSheet();
    });
  });
  document.getElementById('c-leave-empty').addEventListener('change', updateSheet);
  for (let i = 1; i <= 6; i++) {
    document.getElementById('w' + i).addEventListener('input', () => {
      updateWordImagePickers();
      updateSheet();
    });
  }

  const gv = id => parseInt(document.getElementById(id).value, 10);

  function esc(s) {
    return String(s)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function getActiveSlots() {
    return [1,2,3,4,5,6]
      .map(i => ({ slot: i, word: document.getElementById('w'+i).value.trim() }))
      .filter(w => w.word);
  }

  // ── Word image pickers (Färdig bild) ─────────────────────────────
  function updateWordImagePickers() {
    if (appMode !== 'image' || imageMode !== 'existing') return;
    const active = getActiveSlots();
    const container = document.getElementById('word-image-pickers');
    if (!active.length) {
      container.innerHTML = '<p class="no-words-hint">Ange ord ovan för att välja bilder.</p>';
      return;
    }
    container.innerHTML = active.map(({ slot, word }) => {
      const img = wordImages[slot];
      return `<div class="word-image-row">
        <span class="word-label">${esc(word)}</span>
        ${img ? `<img src="images/${esc(img)}" class="word-thumb-small"
                   onclick="openImagePicker(${slot})" title="Klicka för att ändra">` : ''}
        <button class="btn-pick" onclick="openImagePicker(${slot})">${img ? 'Ändra' : 'Välj bild'}</button>
      </div>`;
    }).join('');
  }

  // ── Generate images (Skapa ny bild) ──────────────────────────────
  async function generateImages() {
    const active = getActiveSlots();
    if (!active.length) { alert('Ange minst ett ord.'); return; }

    const btn    = document.getElementById('btn-generate');
    const status = document.getElementById('generate-status');
    btn.disabled = true;
    let done = 0, errors = 0;
    status.textContent = `Genererar 0 / ${active.length}…`;

    await Promise.allSettled(active.map(async ({ slot, word }) => {
      try {
        const res  = await fetch('api/generate.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ word }),
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        wordImages[slot] = data.filename;
        done++;
        status.textContent = `Genererar ${done} / ${active.length}…`;
        updateSheet();
      } catch (e) {
        errors++;
        console.error('Genereringsfel för "' + word + '":', e.message);
      }
    }));

    btn.disabled = false;
    status.textContent = errors
      ? `${done} bilder klara, ${errors} fel (se konsolen).`
      : `Alla ${done} bilder genererade!`;
  }

  // ── Image picker dialog ───────────────────────────────────────────
  async function openImagePicker(slot) {
    pickerSlot = slot;
    const grid = document.getElementById('picker-grid');
    document.getElementById('image-picker-dialog').classList.remove('hidden');
    grid.innerHTML = '<p class="picker-msg">Laddar bilder…</p>';

    try {
      const res    = await fetch('api/images.php');
      const images = await res.json();
      if (!images.length) {
        grid.innerHTML = '<p class="picker-msg">Inga bilder tillgängliga. Ladda upp en bild ovan.</p>';
        return;
      }
      grid.innerHTML = '';
      images.forEach(filename => {
        const div  = document.createElement('div');
        div.className = 'image-picker-thumb';

        const img   = document.createElement('img');
        img.src     = 'images/' + filename;
        img.alt     = filename;
        img.loading = 'lazy';

        const lbl       = document.createElement('span');
        lbl.className   = 'thumb-label';
        lbl.textContent = filename;

        div.appendChild(img);
        div.appendChild(lbl);

        // Left-click: select (suppressed if long-press just fired)
        let suppressClick = false;
        div.addEventListener('click', () => {
          if (!suppressClick) selectImage(filename);
          suppressClick = false;
        });

        // Right-click: context menu
        div.addEventListener('contextmenu', e => {
          e.preventDefault();
          showCtxMenu(filename, e.clientX, e.clientY);
        });

        // Long-press: context menu (touch)
        let lpTimer = null;
        div.addEventListener('touchstart', e => {
          lpTimer = setTimeout(() => {
            suppressClick = true;
            const t = e.touches[0];
            showCtxMenu(filename, t.clientX, t.clientY);
          }, 500);
        }, { passive: true });
        div.addEventListener('touchend',  () => clearTimeout(lpTimer));
        div.addEventListener('touchmove', () => clearTimeout(lpTimer));

        grid.appendChild(div);
      });
    } catch (e) {
      grid.innerHTML = '<p class="picker-msg">Fel vid laddning av bilder.</p>';
    }
  }

  function selectImage(filename) {
    if (pickerSlot !== null) {
      wordImages[pickerSlot] = filename;
      updateWordImagePickers();
      updateSheet();
    }
    closeImagePicker();
  }

  function closeImagePicker() {
    document.getElementById('image-picker-dialog').classList.add('hidden');
    pickerSlot = null;
  }

  async function handleUpload(event) {
    const file = event.target.files[0];
    event.target.value = '';
    if (!file) return;
    const nameEl = document.getElementById('upload-name');
    const fd     = new FormData();
    fd.append('image', file);
    if (nameEl.value.trim()) fd.append('name', nameEl.value.trim());
    nameEl.value = '';
    try {
      const res  = await fetch('api/upload.php', { method: 'POST', body: fd });
      const data = await res.json();
      if (data.error) { alert(data.error); return; }
      if (pickerSlot !== null) openImagePicker(pickerSlot);
    } catch (e) {
      alert('Uppladdningen misslyckades.');
    }
  }

  // ── Image context menu ────────────────────────────────────────────
  let ctxFilename = null;

  function showCtxMenu(filename, x, y) {
    ctxFilename = filename;
    const menu = document.getElementById('img-ctx-menu');
    menu.style.left = x + 'px';
    menu.style.top  = y + 'px';
    menu.classList.remove('hidden');
    const r = menu.getBoundingClientRect();
    if (r.right  > window.innerWidth)  menu.style.left = (x - r.width)  + 'px';
    if (r.bottom > window.innerHeight) menu.style.top  = (y - r.height) + 'px';
  }

  function hideCtxMenu() {
    document.getElementById('img-ctx-menu').classList.add('hidden');
    ctxFilename = null;
  }

  async function ctxDelete() {
    const fn = ctxFilename;
    hideCtxMenu();
    if (!fn || !confirm('Ta bort "' + fn + '"?')) return;
    try {
      const res  = await fetch('api/delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ filename: fn }),
      });
      const data = await res.json();
      if (data.error) { alert(data.error); return; }
      for (const slot in wordImages) {
        if (wordImages[slot] === fn) delete wordImages[slot];
      }
      updateWordImagePickers();
      updateSheet();
      if (pickerSlot !== null) openImagePicker(pickerSlot);
    } catch (e) {
      alert('Fel vid borttagning.');
    }
  }

  async function ctxRename() {
    const fn = ctxFilename;
    hideCtxMenu();
    if (!fn) return;
    const ext     = fn.includes('.') ? fn.slice(fn.lastIndexOf('.')) : '';
    const oldBase = fn.slice(0, fn.length - ext.length);
    const newBase = prompt('Nytt namn:', oldBase);
    if (!newBase || newBase === oldBase) return;
    try {
      const res  = await fetch('api/rename.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ from: fn, to: newBase }),
      });
      const data = await res.json();
      if (data.error) { alert(data.error); return; }
      for (const slot in wordImages) {
        if (wordImages[slot] === fn) wordImages[slot] = data.filename;
      }
      updateWordImagePickers();
      updateSheet();
      if (pickerSlot !== null) openImagePicker(pickerSlot);
    } catch (e) {
      alert('Fel vid namnbyte.');
    }
  }

  document.addEventListener('click', e => {
    if (!e.target.closest('#img-ctx-menu')) hideCtxMenu();
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') hideCtxMenu();
  });

  // ── Sheet rendering helpers ───────────────────────────────────────
  function guideLines(as, xh, bl, ds) {
    return `<div class="gl gl-a" style="top:${as}%"></div>
            <div class="gl gl-x" style="top:${xh}%"></div>
            <div class="gl gl-b" style="top:${bl}%"></div>
            <div class="gl gl-d" style="top:${ds}%"></div>`;
  }

  function ghostSVG(word, bl, fsMM, color) {
    return `<svg style="position:absolute;inset:0;width:100%;height:100%;overflow:visible"
               xmlns="http://www.w3.org/2000/svg">
      <text x="2mm" y="${bl}%"
        font-family="'Patrick Hand', cursive"
        font-size="${fsMM.toFixed(2)}mm"
        fill="${color}">${word}</text>
    </svg>`;
  }

  const _cvs = document.createElement('canvas');
  const _ctx = _cvs.getContext('2d');
  function measureMM(text, fsMM) {
    _ctx.font = `${(fsMM * MM2PX).toFixed(1)}px "Patrick Hand", cursive`;
    return _ctx.measureText(text).width / MM2PX;
  }

  function calcFsMM(words, rh, ew, fs) {
    const exMaxMM = ew - 4;
    let fsMM = rh * fs / 100;
    words.forEach(word => {
      const wMM = measureMM(word, fsMM);
      if (wMM > exMaxMM) fsMM = Math.min(fsMM, fsMM * (exMaxMM / wMM));
    });
    return fsMM;
  }

  // ── Render sheet ──────────────────────────────────────────────────
  function updateSheet() {
    const as = gv('c-as'), xh = gv('c-xh'), bl = gv('c-bl'), ds = gv('c-ds');
    const active = getActiveSlots();
    const sheet  = document.getElementById('sheet');

    if (!active.length) {
      sheet.innerHTML = '<p class="sheet-placeholder">Skriv in ord i panelen till vänster för att se förhandsgranskning</p>';
      return;
    }

    const displaySlots = active.length === 1
      ? Array.from({length: 6}, () => ({ ...active[0] }))
      : active;
    const words       = active.map(w => w.word);
    const fsMM        = calcFsMM(words, RH, EW, FS);
    const baselineMM  = RH * bl / 100;
    const topMM       = Math.max(0, baselineMM - 0.85 * fsMM);
    const wordStyle   = `font-size:${fsMM.toFixed(2)}mm; top:${topMM.toFixed(2)}mm`;
    const pracAvailMM = PRINT_W_MM - EW;
    const L           = guideLines(as, xh, bl, ds);
    const ghostColor  = hex3(gv('c-gray'));

    let html = '';
    displaySlots.forEach(({ slot, word }) => {
      const escaped      = esc(word);
      const wMM          = measureMM(word, fsMM);
      const cellsForWord = Math.max(1, Math.min(NC, Math.floor(pracAvailMM / (wMM + 2))));
      const ghost        = ghostSVG(escaped, bl, fsMM, ghostColor);

      let exContent;
      if (appMode === 'image') {
        const imgFile = wordImages[slot];
        exContent = imgFile
          ? `<img src="images/${esc(imgFile)}" class="ex-img" alt="${escaped}">`
          : `<div class="ex-placeholder">Välj bild</div>`;
      } else {
        exContent = `<div class="ex-word" style="${wordStyle}">${escaped}</div>`;
      }

      const leaveEmpty = document.getElementById('c-leave-empty').checked;
      let cells = '';
      for (let i = 0; i < cellsForWord; i++) {
        const isEmpty = leaveEmpty && i === cellsForWord - 1;
        cells += `<div class="prac-cell">${L}${isEmpty ? '' : ghost}</div>`;
      }

      html += `
<div class="wr" style="height:${RH}mm">
  <div class="ex-cell" style="width:${EW}mm">${L}${exContent}</div>
  <div class="prac-area">${cells}</div>
</div>`;
    });

    sheet.innerHTML = html;
  }

  // ── Mobile bottom-sheet ───────────────────────────────────────────
  const PANEL_PEEK  = 60;
  let   panelExpanded = false;

  function isMobile() { return window.innerWidth < 768; }

  function scaleSheet() {
    const sheet = document.getElementById('sheet');
    if (!isMobile()) { sheet.style.transform = ''; return; }
    const scale = window.innerWidth / (277 * MM2PX);
    sheet.style.transform      = `scale(${scale})`;
    sheet.style.transformOrigin = 'top left';
  }

  function peekOffset() {
    return document.getElementById('input-panel').getBoundingClientRect().height - PANEL_PEEK;
  }

  function setPanel(expanded, animate) {
    panelExpanded = expanded;
    const panel = document.getElementById('input-panel');
    panel.style.transition = (animate !== false)
      ? 'transform .3s cubic-bezier(.32,0,.67,0)' : 'none';
    panel.style.transform = expanded ? 'translateY(0)' : `translateY(${peekOffset()}px)`;
    document.querySelector('.panel-handle-label').textContent = expanded ? 'Dölj' : 'Redigera';
  }

  function togglePanel() { if (isMobile()) setPanel(!panelExpanded); }

  // Touch drag on handle bar
  (function () {
    let startY = 0, startPanelY = 0, dragging = false;

    document.addEventListener('touchstart', e => {
      if (!isMobile() || !e.target.closest('.panel-handle')) return;
      startY     = e.touches[0].clientY;
      startPanelY = new DOMMatrix(
        getComputedStyle(document.getElementById('input-panel')).transform
      ).m42;
      dragging = true;
      document.getElementById('input-panel').style.transition = 'none';
    }, { passive: true });

    document.addEventListener('touchmove', e => {
      if (!dragging) return;
      const dy   = e.touches[0].clientY - startY;
      const maxY = peekOffset();
      document.getElementById('input-panel').style.transform =
        `translateY(${Math.max(0, Math.min(maxY, startPanelY + dy))}px)`;
    }, { passive: true });

    document.addEventListener('touchend', () => {
      if (!dragging) return;
      dragging = false;
      const currentY = new DOMMatrix(
        getComputedStyle(document.getElementById('input-panel')).transform
      ).m42;
      setPanel(currentY < peekOffset() / 2);
    });
  })();

  window.addEventListener('resize', () => {
    scaleSheet();
    if (isMobile()) setPanel(panelExpanded, false);
  });

  // ── Print mode ────────────────────────────────────────────────────
  async function printMode() {
    if (!getActiveSlots().length) { alert('Ange minst ett ord.'); return; }
    await document.fonts.load('16px "Patrick Hand"');
    updateSheet();
    document.getElementById('input-panel').style.display = 'none';
    document.getElementById('toolbar').style.display     = 'flex';
  }

  function editMode() {
    const panel = document.getElementById('input-panel');
    panel.style.display = 'block';
    document.getElementById('toolbar').style.display = 'none';
    if (isMobile()) requestAnimationFrame(() => setPanel(false, false));
  }

  // Initialize
  scaleSheet();
  updateSheet();
  document.fonts.ready.then(updateSheet);
</script>
</body>
</html>
