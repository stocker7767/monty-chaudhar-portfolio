<?php
/** The portfolio homepage. */
defined('ABSPATH') || exit;
$projects = monty_portfolio_projects();
$featured_count = count(array_filter($projects, static function ($project) {
  return !empty($project['featured']); }));
$hero_projects = monty_portfolio_hero_projects();
get_header();
?>
<main id="main">
  <section class="hero shell" id="top" aria-labelledby="hero-title">
    <div class="hero-copy">
      <div class="eyebrow"><span class="status-dot"></span>
        <?php echo esc_html(monty_portfolio_setting('eyebrow')); ?></div>
      <h1 id="hero-title"><span class="hero-greeting">Hi, I’m</span><em
          class="hero-name"><?php echo esc_html(monty_portfolio_setting('first_name')); ?><span>.</span></em><span
          class="hero-role">I build websites.<br>You get to <span class="marked-word">explore them.<svg
              viewBox="0 0 330 20" aria-hidden="true">
              <path d="M3 12C65 3 196 3 326 10M32 17C123 9 227 10 295 15" />
            </svg></span></span></h1>
      <p class="hero-description"><?php echo esc_html(monty_portfolio_setting('hero_description')); ?></p>
      <div class="hero-actions"><a class="button button-lime magnetic" href="#work">Explore my work <span
            class="round-arrow">↗</span></a><a class="text-link" href="#about">Meet the developer <span>↘</span></a>
      </div>
      <p class="availability"><span class="status-dot"></span>
        <?php echo esc_html(monty_portfolio_setting('availability')); ?></p>
    </div>

    <div class="hero-scene" id="hero-scene">
      <span class="scene-label"><span class="live-indicator"></span> A REAL WEBSITE. RIGHT HERE.</span>
      <div class="orbit orbit-one" aria-hidden="true"></div>
      <div class="orbit orbit-two" aria-hidden="true"></div>
      <div class="hero-browser" id="hero-browser">
        <div class="browser-top"><span class="window-dots"><i></i><i></i><i></i></span><span
            id="hero-address">selected-work / chirpy</span><span>↗</span></div>
        <div class="hero-browser-content" id="hero-art"><noscript>
            <div class="fallback-hero">Enable JavaScript for the interactive preview.</div>
          </noscript></div>
        <div class="hero-browser-footer"><span>MADE TO BE EXPLORED</span><button id="hero-explore">Open project
            ↗</button></div>
      </div>
      <button class="sticker sticker-html" data-sticker
        aria-label="Drag HTML sticker, or use arrow keys">&lt;/&gt;<small>HTML</small></button>
      <button class="sticker sticker-js" data-sticker
        aria-label="Drag JavaScript sticker, or use arrow keys">JS<small>MAKE IT MOVE</small></button>
      <a class="portrait-note" href="#about"><img src="<?php echo esc_url(monty_portfolio_portrait()); ?>"
          alt="<?php echo esc_attr(monty_portfolio_setting('name')); ?>" width="80" height="80"><span>The
          person<br>behind the pixels <i>↘</i></span></a>
      <div class="scene-caption"><span class="hand-arrow" aria-hidden="true">↖</span> Pick one. Have a look around.
      </div>
      <div class="hero-switcher" role="group" aria-label="Featured project preview">
        <?php foreach ($hero_projects as $index => $project): ?><button
            data-hero="<?php echo esc_attr($project['id']); ?>"
            aria-pressed="<?php echo 0 === $index ? 'true' : 'false'; ?>"><span><?php echo esc_html(sprintf('%02d', $index + 1)); ?></span>
            <?php echo esc_html($project['name']); ?></button><?php endforeach; ?></div>
    </div>
    <div class="hero-bottom"><a href="#work">THERE’S MORE DOWN HERE <span>↓</span></a><span>SCROLL. CLICK. GET A LITTLE
        CURIOUS.</span></div>
  </section>

  <div class="skills-ribbon" aria-label="Core skills">
    <div>
      <span>HTML5</span><i>✳</i><span>CSS3</span><i>✳</i><span>JAVASCRIPT</span><i>✳</i><span>WORDPRESS</span><i>✳</i><span>RESPONSIVE
        DESIGN</span><i>✳</i><span>GIT & GITHUB</span><i>✳</i></div>
  </div>

  <section class="work-section shell section-space" id="work" aria-labelledby="work-title">
    <div class="section-heading reveal">
      <div>
        <p class="eyebrow">01 / OPEN FOR EXPLORATION</p>
        <h2 id="work-title">The work.<br><em>In the browser.</em><span class="heading-spark" aria-hidden="true">✳</span>
        </h2>
      </div>
      <p>Browse the websites. Try the interactions.<br>Or open the source and see how they work.</p>
    </div>
    <div class="work-toolbar">
      <div class="filters" role="group" aria-label="Filter projects"><button data-filter="featured"
          aria-pressed="true">Featured <span><?php echo esc_html((string) $featured_count); ?></span></button><button
          data-filter="all" aria-pressed="false">All work
          <span><?php echo esc_html((string) count($projects)); ?></span></button><button data-filter="wordpress"
          aria-pressed="false">WordPress</button><button data-filter="business" aria-pressed="false">Business
          websites</button><button data-filter="interactive" aria-pressed="false">Interactive</button><button
          data-filter="practice" aria-pressed="false">Early work</button></div><label class="search-box"><span
          aria-hidden="true">⌕</span><input id="project-search" type="search" placeholder="Find a project…"
          aria-label="Search projects"><kbd>/</kbd></label>
    </div>
    <div class="gallery-meta">
      <p class="result-count" id="result-count" role="status" aria-live="polite"></p>
      <div class="view-switch" role="group" aria-label="Project layout"><button id="grid-view" aria-pressed="true"
          aria-label="Grid view">◫</button><button id="list-view" aria-pressed="false" aria-label="List view">☷</button>
      </div>
    </div>
    <div class="project-grid" id="project-grid"></div>
    <div class="empty-state" id="empty-state" hidden><span>⌕</span>
      <h3>No matching projects.</h3>
      <p>Try a name, a technology or a different filter.</p><button class="button button-outline" id="reset-search">Show
        all work ↗</button>
    </div>
    <noscript>
      <ul class="no-js-projects"><?php foreach ($projects as $project): ?>
          <li><strong><?php echo esc_html($project['name']); ?></strong>
            <p><?php echo esc_html($project['line']); ?></p><?php if ($project['path']): ?><a
                href="<?php echo esc_url($project['path']); ?>">View website
                ↗</a><?php endif; ?><?php if (!empty($project['codeUrl'])): ?><a
                href="<?php echo esc_url($project['codeUrl']); ?>">View code ↗</a><?php endif; ?>
          </li><?php endforeach; ?>
      </ul>
    </noscript>
    <div class="work-end"><span>From the first experiment to the latest build.</span><button class="text-link"
        id="show-all">Open the full collection <span>↗</span></button></div>
  </section>

  <section class="playground-section" id="playground" aria-labelledby="playground-title">
    <div class="shell playground-layout">
      <div class="playground-copy reveal">
        <p class="eyebrow">02 / A LITTLE PLAYGROUND</p>
        <h2 id="playground-title">Don’t just look.<br><em>Have a play.</em></h2>
        <p>A small experiment in making an interface feel right. Change the colour. Round the corners. Give it a click.
        </p>
        <div class="play-controls">
          <fieldset>
            <legend>Pick a personality</legend>
            <div class="swatches"><button data-colour="#d0fa79" style="--swatch:#d0fa79" aria-label="Lime"
                aria-pressed="true"></button><button data-colour="#b6a5f5" style="--swatch:#b6a5f5" aria-label="Lilac"
                aria-pressed="false"></button><button data-colour="#ffad87" style="--swatch:#ffad87" aria-label="Peach"
                aria-pressed="false"></button><button data-colour="#93d7e8" style="--swatch:#93d7e8" aria-label="Sky"
                aria-pressed="false"></button></div>
          </fieldset><label class="range-label" for="corner-range">Round the corners <output
              id="corner-value">18px</output><input type="range" id="corner-range" min="0" max="48" value="18"></label>
        </div>
      </div>
      <div class="play-stage" id="play-stage"><span class="stage-label">YOUR LITTLE CORNER OF THE INTERNET</span>
        <div class="play-grid" aria-hidden="true"></div>
        <div class="play-card" id="play-card">
          <div class="play-card-top"><span>INTERACTION / 001</span><span>↗</span></div><span class="play-star"
            aria-hidden="true">✳</span>
          <h3>Made you<br><em>click.</em></h3>
          <p>Small details. Big personality.</p><button id="play-button">Go on, try me <span>↗</span></button>
        </div>
        <p class="play-feedback" id="play-feedback" role="status">A satisfying interaction is a click away.</p>
      </div>
    </div>
  </section>

  <section class="about-section shell section-space" id="about" aria-labelledby="about-title">
    <div class="about-intro reveal">
      <p class="eyebrow">03 / A BIT MORE HUMAN</p>
      <h2 id="about-title">There’s a person<br><em>behind the code.</em></h2>
    </div>
    <div class="about-layout">
      <div class="portrait-composition reveal">
        <div class="portrait-frame"><img src="<?php echo esc_url(monty_portfolio_portrait()); ?>"
            alt="<?php echo esc_attr(monty_portfolio_setting('name')); ?>" width="501" height="570" loading="lazy">
          <div class="portrait-caption">
            <span><?php echo esc_html(monty_portfolio_setting('name')); ?></span><small><?php echo esc_html(monty_portfolio_setting('location')); ?>
              ↗</small></div>
        </div><span class="portrait-scribble">Hello again. <span aria-hidden="true">↖</span></span><span
          class="photo-coordinate">A DEVELOPER, ALWAYS LEARNING.</span>
      </div>
      <div class="about-story">
        <p class="large-copy"><?php echo nl2br(esc_html(monty_portfolio_setting('about_lead'))); ?></p>
        <p><?php echo esc_html(monty_portfolio_setting('about_one')); ?></p>
        <p><?php echo esc_html(monty_portfolio_setting('about_two')); ?></p>
        <div class="education-note"><span>↗</span>
          <div>
            <strong><?php echo esc_html(monty_portfolio_setting('education')); ?></strong><span><?php echo esc_html(monty_portfolio_setting('university')); ?></span>
          </div>
        </div>
        <div class="capabilities">
          <details open>
            <summary><span>01</span> Front-end foundations <i>+</i></summary>
            <p>HTML5, CSS3, Flexbox, Grid and responsive layouts. Clear structure and interfaces that work across screen
              sizes.</p>
          </details>
          <details>
            <summary><span>02</span> JavaScript & interactions <i>+</i></summary>
            <p>DOM interactions, event handling, filters, browser storage and the small details that bring a page to
              life.</p>
          </details>
          <details>
            <summary><span>03</span> WordPress development <i>+</i></summary>
            <p>Custom theme project work, PHP templates, content management and connected user journeys.</p>
          </details>
          <details>
            <summary><span>04</span> Delivery & collaboration <i>+</i></summary>
            <p>Git and GitHub, client feedback, task planning and explaining technical ideas in plain language.</p>
          </details>
        </div><a class="text-link about-link" href="<?php echo esc_url(monty_portfolio_setting('linkedin')); ?>"
          target="_blank" rel="noopener noreferrer">More of my story on LinkedIn <span>↗</span></a>
      </div>
    </div>
  </section>

  <section class="contact-section" id="contact" aria-labelledby="contact-title">
    <div class="shell">
      <div class="contact-top">
        <p class="eyebrow"><span class="status-dot"></span> 04 / LET’S MAKE SOMETHING GOOD</p>
        <span><?php echo esc_html(monty_portfolio_setting('contact_location')); ?></span>
      </div><a class="contact-headline" href="<?php echo esc_url('mailto:' . monty_portfolio_setting('email')); ?>">
        <h2 id="contact-title">Have a role<br>in <em>mind?</em></h2><span class="contact-arrow"
          aria-hidden="true">↗</span>
      </a>
      <div class="contact-bottom">
        <div><a class="email-link"
            href="<?php echo esc_url('mailto:' . monty_portfolio_setting('email')); ?>"><?php echo esc_html(monty_portfolio_setting('email')); ?></a><button
            id="copy-email" class="copy-button" aria-label="Copy email address">Copy email ⧉</button></div>
        <div class="social-links"><a href="<?php echo esc_url(monty_portfolio_setting('github')); ?>"
            target="_blank" rel="noopener noreferrer">GitHub ↗</a><a
            href="<?php echo esc_url(monty_portfolio_setting('linkedin')); ?>" target="_blank"
            rel="noopener noreferrer">LinkedIn ↗</a></div>
      </div>
    </div>
  </section>
</main>
<?php get_footer(); ?>