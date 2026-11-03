<?php defined('ABSPATH') || exit; ?>
<dialog id="project-dialog" class="project-dialog" aria-labelledby="dialog-title">
  <div class="dialog-top">
    <div><span class="eyebrow" id="dialog-type"></span>
      <h2 id="dialog-title"></h2>
    </div><button class="close-button" id="dialog-close" aria-label="Close project">×</button>
  </div>
  <div class="dialog-tabs" role="tablist" aria-label="Project information"><button id="overview-tab" role="tab"
      aria-controls="project-overview" aria-selected="true" data-tab="overview">The project</button><button
      id="preview-tab" role="tab" aria-controls="project-preview" aria-selected="false" data-tab="preview">Live
      preview</button><a id="dialog-code" href="https://github.com/stocker7767" target="_blank"
      rel="noopener noreferrer">View code ↗</a></div>
  <div id="project-overview" role="tabpanel" aria-labelledby="overview-tab">
    <div id="dialog-cover" class="dialog-cover"></div>
    <div class="dialog-details">
      <div>
        <p class="eyebrow">WHAT I WORKED ON</p>
        <p id="dialog-description"></p>
        <p id="dialog-note" class="project-note"></p>
      </div>
      <div>
        <p class="eyebrow">BUILT WITH</p>
        <div id="dialog-stack" class="tech-tags"></div><a id="dialog-live" class="button button-lime" href="#">Open
          website <span>↗</span></a>
      </div>
    </div>
  </div>
  <div id="project-preview" role="tabpanel" aria-labelledby="preview-tab" hidden>
    <div class="preview-toolbar">
      <div role="group" aria-label="Preview screen size"><button id="desktop-preview"
          aria-pressed="true">Desktop</button><button id="mobile-preview" aria-pressed="false">Mobile</button></div><a
        id="preview-open" href="#" target="_blank" rel="noopener noreferrer">Open in a new tab ↗</a>
    </div>
    <div id="preview-surface" class="preview-surface"><iframe id="project-frame" title="Project website preview"
        sandbox="allow-scripts allow-same-origin allow-forms allow-popups" loading="lazy"></iframe></div>
    <p class="preview-caption">Portfolio demo · Use the site navigation to explore its pages.</p>
  </div>
</dialog>
<dialog id="command-dialog" class="command-dialog" aria-labelledby="command-title">
  <div class="command-head"><label for="command-search" id="command-title">Where would you like to go?</label><button
      id="command-close" aria-label="Close quick navigation">Esc</button></div><input id="command-search" type="search"
    placeholder="Search projects or jump to a section…" autocomplete="off">
  <div id="command-results"></div>
  <p>Choose a destination · Escape to close</p>
</dialog>
<div id="toast" class="toast" role="status" aria-live="polite"></div>