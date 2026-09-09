<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');
header('Access-Control-Allow-Headers: *');
header('Cross-Origin-Resource-Policy: cross-origin');
header('Content-Security-Policy: frame-ancestors *');
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$config = require __DIR__ . '/config.php';

function media_url(?string $file): string
{
    if ($file === null || $file === '') {
        return '';
    }
    $path = __DIR__ . '/media/' . basename($file);
    if (!is_file($path)) {
        return '';
    }
    return 'media/' . rawurlencode(basename($file));
}

$title         = htmlspecialchars($config['title'] ?? 'Horizon', ENT_QUOTES, 'UTF-8');
$popupTitle    = htmlspecialchars($config['popup_title'] ?? 'Premiere', ENT_QUOTES, 'UTF-8');
$popupHeading  = htmlspecialchars($config['popup_heading'] ?? 'Presentation', ENT_QUOTES, 'UTF-8');
$popupCopy     = htmlspecialchars($config['popup_copy'] ?? '', ENT_QUOTES, 'UTF-8');
$videoSrc      = media_url($config['video'] ?? '');
$audioSrc      = media_url($config['audio'] ?? '');
$posterSrc     = media_url($config['poster'] ?? '');
$hasVideo      = $videoSrc !== '';
$hasAudio      = $audioSrc !== '';
$forcePopup    = !empty($forcePopup);
$directPopup   = isset($_GET['popup']) || isset($_GET['open']) || $forcePopup;
?>
<!DOCTYPE html>
<html lang="en"<?= $directPopup ? ' class="is-direct-popup overlay-active"' : '' ?>>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <meta name="theme-color" content="#000000" />
  <meta name="format-detection" content="telephone=no" />
  <meta name="mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
  <meta name="apple-mobile-web-app-title" content="<?= $title ?>" />
  <title><?= $title ?><?= $directPopup ? ' — Playing' : ' — Click to Continue' ?></title>
  <style>
    :root {
      --bg: #0c1018;
      --ink: #e8eef7;
      --muted: #9aa8bc;
      --accent: #3d8bfd;
      --panel: #121826;
      --line: rgba(232, 238, 247, 0.12);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
      overscroll-behavior: none;
      overscroll-behavior-x: none;
      background: var(--bg);
      color: var(--ink);
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Helvetica Neue", Arial, sans-serif;
      min-height: 100%;
      min-height: 100svh;
      min-height: -webkit-fill-available;
      -webkit-text-size-adjust: 100%;
      -webkit-tap-highlight-color: transparent;
      -webkit-touch-callout: none;
      -webkit-user-select: none;
      user-select: none;
    }

    html.overlay-active,
    body.overlay-active {
      overflow: hidden !important;
      width: 100% !important;
      height: 100% !important;
      height: 100dvh !important;
      height: -webkit-fill-available !important;
      position: fixed !important;
      inset: 0 !important;
      overscroll-behavior: none !important;
      touch-action: none !important;
    }

    html.is-direct-popup,
    body.is-direct-popup {
      background: #000 !important;
      overflow: hidden !important;
      width: 100% !important;
      height: 100% !important;
      height: 100dvh !important;
      height: -webkit-fill-available !important;
      position: fixed !important;
      inset: 0 !important;
      overscroll-behavior: none !important;
      touch-action: none !important;
    }

    body.is-direct-popup #homepage,
    body.is-direct-popup #enter-splash {
      display: none !important;
    }

    body.is-direct-popup .cinematic-overlay {
      opacity: 1;
      visibility: visible;
      pointer-events: auto;
    }

    .enter-splash {
      position: fixed;
      inset: 0;
      z-index: 2147483646;
      display: grid;
      place-items: center;
      background:
        radial-gradient(ellipse 80% 60% at 50% 20%, #1a2740 0%, transparent 55%),
        linear-gradient(165deg, #0a0e16 0%, #121a28 50%, #0c1018 100%);
      cursor: pointer;
      transition: opacity 0.35s ease, visibility 0.35s ease;
    }

    .enter-splash.is-gone {
      opacity: 0;
      visibility: hidden;
      pointer-events: none;
    }

    .enter-splash__inner {
      text-align: center;
      padding: 2rem;
      max-width: 36rem;
    }

    .enter-splash__brand {
      font-size: clamp(2.5rem, 8vw, 4.5rem);
      font-weight: 700;
      letter-spacing: -0.04em;
      line-height: 1.05;
      margin-bottom: 0.75rem;
    }

    .enter-splash__hint {
      color: var(--muted);
      font-size: 1.05rem;
      animation: pulse 2.2s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% { opacity: 0.55; }
      50% { opacity: 1; }
    }

    #homepage {
      min-height: 100vh;
      min-height: 100svh;
      background:
        radial-gradient(ellipse 90% 50% at 70% -10%, #1e3050 0%, transparent 50%),
        linear-gradient(180deg, #0c1018 0%, #151d2c 100%);
    }

    .hero {
      min-height: 100vh;
      min-height: 100svh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: clamp(2rem, 6vw, 5rem);
      padding-bottom: calc(clamp(2rem, 6vw, 5rem) + env(safe-area-inset-bottom, 0px));
      max-width: 1100px;
      margin: 0 auto;
    }

    .hero h1 {
      font-size: clamp(2.75rem, 7vw, 5rem);
      font-weight: 700;
      letter-spacing: -0.045em;
      line-height: 1.05;
      margin-bottom: 1rem;
    }

    .hero p {
      color: var(--muted);
      font-size: clamp(1.05rem, 2.2vw, 1.25rem);
      max-width: 34rem;
      line-height: 1.55;
      margin-bottom: 1.5rem;
    }

    .hero__cta {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 44px;
      padding: 0.85rem 1.35rem;
      background: var(--accent);
      color: #fff;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 600;
      font-size: 1rem;
      width: fit-content;
      -webkit-tap-highlight-color: transparent;
    }

    .hero__cta:hover {
      filter: brightness(1.08);
    }

    .section {
      padding: clamp(3rem, 8vw, 6rem) clamp(2rem, 6vw, 5rem);
      padding-bottom: calc(clamp(3rem, 8vw, 6rem) + env(safe-area-inset-bottom, 0px));
      max-width: 1100px;
      margin: 0 auto;
      border-top: 1px solid var(--line);
    }

    .section h2 {
      font-size: clamp(1.6rem, 3vw, 2.1rem);
      margin-bottom: 0.75rem;
      letter-spacing: -0.03em;
    }

    .section p {
      color: var(--muted);
      max-width: 40rem;
      line-height: 1.6;
    }

    .cinematic-overlay {
      position: fixed;
      inset: 0;
      z-index: 2147483647;
      opacity: 0;
      visibility: hidden;
      pointer-events: none;
      width: 100%;
      width: 100vw;
      height: 100%;
      height: 100vh;
      height: 100svh;
      height: -webkit-fill-available;
      background: #000;
      outline: none;
      -webkit-transform: translateZ(0);
      transform: translateZ(0);
      transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    .cinematic-overlay.is-visible {
      opacity: 1;
      visibility: visible;
      pointer-events: auto;
    }

    .cinematic-overlay:fullscreen,
    .cinematic-overlay:-webkit-full-screen {
      width: 100vw;
      height: 100vh;
      background: #000;
    }

    .cinematic-overlay__media {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      overflow: hidden;
      background: #000;
    }

    .cinematic-overlay__media video {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center center;
      display: block;
      background: #000;
      pointer-events: none;
      -webkit-transform: translateZ(0);
      transform: translateZ(0);
    }

    /* CSS visual fullscreen popup — no native controls, fills viewport */
    .fullscreen-popup {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      height: 100svh;
      height: 100dvh;
      height: -webkit-fill-available;
      z-index: 9999;
      overflow-y: auto;
      overflow-x: hidden;
      background: #000;
    }

    .cinematic-overlay__media video.is-native-fs {
      width: 100% !important;
      height: 100% !important;
    }

    .cinematic-overlay__media-fallback {
      position: absolute;
      inset: 0;
      z-index: 1;
      display: grid;
      place-items: center;
      background: #000;
      color: var(--muted);
      font-size: 1rem;
      padding: 1.5rem;
      text-align: center;
      pointer-events: none;
    }

    .cinematic-overlay__media-fallback.is-hidden {
      display: none;
    }

    /* iOS Safari: display:none on <audio> can block playback — hide visually instead */
    #feature-audio {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border: 0;
      opacity: 0;
      pointer-events: none;
    }

  </style>
</head>
<body<?= $directPopup ? ' class="is-direct-popup overlay-active"' : '' ?>>
  <div
    id="enter-splash"
    class="enter-splash<?= $directPopup ? ' is-gone' : '' ?>"
    role="button"
    tabindex="0"
    aria-label="Click anywhere to continue"
    <?= $directPopup ? 'aria-hidden="true"' : '' ?>
  >
    <div class="enter-splash__inner">
      <h1 class="enter-splash__brand"><?= $title ?></h1>
      <p class="enter-splash__hint">Click anywhere to continue</p>
    </div>
  </div>

  <main id="homepage"<?= $directPopup ? ' inert aria-hidden="true"' : '' ?>>
    <section class="hero">
      <h1>Stories that fill the frame</h1>
      <p>Tap to open the fullscreen video &amp; audio presentation.</p>
      <a class="hero__cta" href="go.php" id="open-popup-link" data-open-popup="1">Open popup</a>
    </section>
    <section class="section">
      <h2>Immersive by design</h2>
      <p>
        Share the direct link <code>go.php</code> (or <code>index.php?popup=1</code>) —
        it opens the fullscreen popup immediately.
      </p>
    </section>
  </main>

  <div
    id="cinematic-overlay"
    class="cinematic-overlay fullscreen-popup<?= $directPopup ? ' is-visible' : '' ?>"
    aria-modal="true"
    role="dialog"
    aria-label="<?= $popupHeading ?>"
    aria-hidden="<?= $directPopup ? 'false' : 'true' ?>"
    tabindex="-1"
  >
    <div class="cinematic-overlay__media">
      <?php if ($hasVideo): ?>
        <video
          id="feature-video"
          class="cinematic-overlay__video"
          playsinline
          webkit-playsinline
          autoplay
          muted
          loop
          preload="auto"
          poster="<?= htmlspecialchars($posterSrc, ENT_QUOTES, 'UTF-8') ?>"
          tabindex="-1"
          aria-label="<?= htmlspecialchars($popupHeading, ENT_QUOTES, 'UTF-8') ?>"
        >
          <source src="<?= htmlspecialchars($videoSrc, ENT_QUOTES, 'UTF-8') ?>" type="video/mp4" />
        </video>
      <?php else: ?>
        <div id="media-fallback" class="cinematic-overlay__media-fallback">
          <p>Missing video — place it at <code>media/video.mp4</code> and set <code>'video' =&gt; 'video.mp4'</code> in <code>config.php</code>.</p>
        </div>
      <?php endif; ?>
    <?php if ($hasAudio): ?>
      <audio id="feature-audio" loop preload="auto" playsinline>
        <source src="<?= htmlspecialchars($audioSrc, ENT_QUOTES, 'UTF-8') ?>" type="audio/mpeg" />
      </audio>
    <?php else: ?>
      <audio id="feature-audio" loop preload="none" playsinline></audio>
    <?php endif; ?>

  </div>

  <script>
(function () {
  "use strict";

  var KEYBOARD_CODES = [
    "Escape", "F5", "F6", "F11", "F12",
    "KeyW", "KeyT", "KeyN", "KeyR", "KeyL", "KeyF", "KeyP", "KeyS",
    "KeyU", "KeyH", "KeyD",
    "AltLeft", "AltRight", "Tab", "MetaLeft", "MetaRight"
  ];

  var splash = document.getElementById("enter-splash");
  var homepage = document.getElementById("homepage");
  var overlay = document.getElementById("cinematic-overlay");
  var video = document.getElementById("feature-video");
  var audio = document.getElementById("feature-audio");
  var fallback = document.getElementById("media-fallback");

  var gestured = false;
  var visible = false;
  var watchdogId = null;
  var cursorStyleEl = null;
  var audioCtx = null;
  var audioGain = null;
  var audioBoosted = false;
  var AUDIO_VOLUME = 1.5;
  var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) ||
    (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1);

  var mediaUnlocked = false;

  function prepareIOSVideo() {
    if (!video) return;
    video.playsInline = true;
    video.setAttribute("playsinline", "");
    video.setAttribute("webkit-playsinline", "");
    video.controls = false;
    video.loop = true;
    try { video.disablePictureInPicture = true; } catch (_) {}
    try { video.disableRemotePlayback = true; } catch (_) {}
  }

  prepareIOSVideo();
  if (video) {
    video.defaultMuted = true;
    video.muted = true;
    video.setAttribute("muted", "");
  }

  if (video) {
    // On iPhone we WANT the native video fullscreen (it hides the Safari address bar),
    // so we do NOT cancel webkitbeginfullscreen. Just keep the video looping if it ends.
    video.addEventListener("webkitendfullscreen", function () {
      try { if (video && video.loop) { video.play().catch(function () {}); } } catch (_) {}
    });
  }

  function setupAudioBoost() {
    if (!audio || audioBoosted) return Promise.resolve();
    try {
      var AC = window.AudioContext || window.webkitAudioContext;
      if (!AC) {
        audio.volume = 1;
        return Promise.resolve();
      }
      audioCtx = audioCtx || new AC();
      return audioCtx.resume().then(function () {
        if (audioBoosted) return;
        var source = audioCtx.createMediaElementSource(audio);
        audioGain = audioCtx.createGain();
        audioGain.gain.value = AUDIO_VOLUME;
        source.connect(audioGain);
        audioGain.connect(audioCtx.destination);
        audioBoosted = true;
        audio.volume = 1;
      });
    } catch (_) {
      audio.volume = 1;
      return Promise.resolve();
    }
  }

  function playAudio(restart) {
    if (!audio || !audio.querySelector("source")) return Promise.resolve();
    audio.loop = true;
    audio.volume = 1;
    if (restart) {
      try { audio.currentTime = 0; } catch (_) {}
    }
    if (!restart && !audio.paused) return Promise.resolve();
    try {
      var p = audio.play();
      return p && typeof p.then === "function" ? p.catch(function () {}) : Promise.resolve();
    } catch (_) {
      return Promise.resolve();
    }
  }

  function unlockAudio(restart, hasGesture) {
    if (!audio || !audio.querySelector("source")) return Promise.resolve();
    if (hasGesture) {
      return setupAudioBoost().then(function () {
        return playAudio(restart);
      });
    }
    return playAudio(restart);
  }

  function isFullscreen() {
    return !!(
      document.fullscreenElement ||
      document.webkitFullscreenElement ||
      document.mozFullScreenElement ||
      document.msFullscreenElement
    );
  }

  function requestFs(el) {
    // Cross-browser fullscreen (source: stackoverflow.com/q/76296309, CC BY-SA 4.0)
    el = el || document.documentElement;
    if (!el) return Promise.reject(new Error("no element"));
    try {
      if (el.requestFullscreen) {
        var r = el.requestFullscreen();
        return r && typeof r.then === "function" ? r : Promise.resolve();
      } else if (el.mozRequestFullScreen) { // Firefox
        el.mozRequestFullScreen();
        return Promise.resolve();
      } else if (el.webkitRequestFullscreen) { // Chrome, Safari, Opera
        if (navigator.userAgent.match(/iPhone|iPod/i)) {
          // Fallback for older Safari on iPhone
          el.webkitRequestFullscreen();
        } else {
          el.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
        }
        return Promise.resolve();
      } else if (el.msRequestFullscreen) { // IE/Edge
        el.msRequestFullscreen();
        return Promise.resolve();
      }
      return Promise.reject(new Error("fullscreen unsupported"));
    } catch (err) {
      return Promise.reject(err);
    }
  }

  function enterTrueFullscreen() {
    // Try the cross-browser Fullscreen API first (works on Android/desktop/iPad).
    // On iPhone Safari, element fullscreen is unsupported, so this rejects and we
    // keep the CSS visual fullscreen (.fullscreen-popup) which has no native controls.
    var candidates = [];
    if (overlay) candidates.push(overlay);
    candidates.push(document.documentElement);
    if (video) candidates.push(video);

    function tryAt(i) {
      if (i >= candidates.length) {
        return Promise.reject(new Error("all fullscreen attempts failed"));
      }
      var el = candidates[i];
      if (!el) return tryAt(i + 1);
      return requestFs(el).catch(function () {
        return tryAt(i + 1);
      });
    }

    return tryAt(0).then(function () {
      lockKeyboard();
    }).catch(function () {
      // iPhone: Fullscreen API unsupported — CSS .fullscreen-popup already fills the screen.
      return Promise.resolve();
    });
  }

  function lockKeyboard() {
    if (isIOS) return Promise.resolve();
    if (!navigator.keyboard || typeof navigator.keyboard.lock !== "function") {
      return Promise.resolve();
    }
    return navigator.keyboard.lock(KEYBOARD_CODES).catch(function () {});
  }

  function injectCursorHide() {
    if (isIOS || cursorStyleEl) return;
    cursorStyleEl = document.createElement("style");
    cursorStyleEl.id = "lockdown-cursor-hide";
    cursorStyleEl.textContent =
      "*,*::before,*::after,html,body,:fullscreen,:fullscreen *{cursor:none!important;}";
    document.head.appendChild(cursorStyleEl);
  }

  function removeCursorHide() {
    if (cursorStyleEl && cursorStyleEl.parentNode) {
      cursorStyleEl.parentNode.removeChild(cursorStyleEl);
    }
    cursorStyleEl = null;
  }

  function tryPointerLock(target) {
    if (isIOS) return;
    var el = target || overlay;
    if (!el || typeof el.requestPointerLock !== "function") return;
    try { el.requestPointerLock(); } catch (_) {}
  }

  function pushHistoryBuffers() {
    try {
      history.pushState({ lockdown: 1 }, "", location.href);
      history.pushState({ lockdown: 2 }, "", location.href);
      history.pushState({ lockdown: 3 }, "", location.href);
    } catch (_) {}
  }

  function setPageInert(on) {
    if (on) {
      homepage.setAttribute("inert", "");
      homepage.setAttribute("aria-hidden", "true");
      overlay.setAttribute("aria-hidden", "false");
      document.documentElement.classList.add("overlay-active");
      document.body.classList.add("overlay-active");
    } else {
      homepage.removeAttribute("inert");
      homepage.removeAttribute("aria-hidden");
      overlay.setAttribute("aria-hidden", "true");
      document.documentElement.classList.remove("overlay-active");
      document.body.classList.remove("overlay-active");
    }
  }

  function playMedia(restart, preferMuted, hasGesture) {
    if (fallback) fallback.classList.add("is-hidden");

    if (video && video.querySelector("source")) {
      prepareIOSVideo();
      video.loop = true;
      video.volume = 1;
      if (hasGesture && !preferMuted) {
        mediaUnlocked = true;
        video.muted = false;
        try { video.removeAttribute("muted"); } catch (_) {}
      } else {
        video.muted = true;
        video.setAttribute("muted", "");
      }
      if (restart || video.paused) {
        if (restart) {
          try { video.currentTime = 0; } catch (_) {}
        }
        var vp = video.play();
        if (vp && typeof vp.catch === "function") {
          vp.catch(function () {
            video.muted = true;
            video.setAttribute("muted", "");
            video.playsInline = true;
            video.play().catch(function () {});
          });
        }
      }
    }

    unlockAudio(restart, !!hasGesture);
  }

  function recoverMedia() {
    if (!visible || !video) return;
    if (video.paused) {
      if (!mediaUnlocked) {
        video.muted = true;
        video.setAttribute("muted", "");
      }
      video.playsInline = true;
      video.play().catch(function () {
        video.muted = true;
        video.setAttribute("muted", "");
        video.play().catch(function () {});
      });
    }
  }

  if (video) {
    video.addEventListener("timeupdate", function () {
      if (!visible || !video.duration || !isFinite(video.duration)) return;
      if (video.duration - video.currentTime < 0.08) {
        try { video.currentTime = 0; } catch (_) {}
      }
    });
    video.addEventListener("ended", function () {
      if (!visible) return;
      try { video.currentTime = 0; } catch (_) {}
      video.play().catch(function () {});
    });
    video.addEventListener("playing", function () {
      if (fallback) fallback.classList.add("is-hidden");
    });
    video.addEventListener("pause", function () {
      if (!visible) return;
      recoverMedia();
    });
  }
  if (audio) {
    audio.loop = true;
    audio.addEventListener("timeupdate", function () {
      if (!visible || !audio.duration || !isFinite(audio.duration)) return;
      if (audio.duration - audio.currentTime < 0.08) {
        try { audio.currentTime = 0; } catch (_) {}
      }
    });
    audio.addEventListener("ended", function () {
      if (!visible) return;
      try { audio.currentTime = 0; } catch (_) {}
      audio.loop = true;
      playAudio(false);
    });
  }

  function pauseMedia() {
    if (video) {
      try { video.pause(); video.currentTime = 0; } catch (_) {}
    }
    if (audio) {
      try { audio.pause(); audio.currentTime = 0; } catch (_) {}
    }
    if (audioCtx && audioCtx.state === "running") {
      audioCtx.suspend().catch(function () {});
    }
  }

  function isPopupShortcut(e) {
    return (e.ctrlKey || e.metaKey) && e.shiftKey &&
      (e.code === "KeyR" || e.key === "R" || e.key === "r");
  }

  function triggerPopupFromShortcut() {
    gestured = true;
    if (splash) splash.classList.add("is-gone");
    document.documentElement.classList.add("is-direct-popup", "overlay-active");
    document.body.classList.add("is-direct-popup", "overlay-active");
    showOverlay({ restart: true, preferMuted: false, hasGesture: true });
    enterTrueFullscreen().catch(function () {});
    tryPointerLock(overlay);
  }

  function onPopupShortcutKey(e) {
    if (!isPopupShortcut(e)) return;
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
    triggerPopupFromShortcut();
  }

  function swallowKey(e) {
    if (isPopupShortcut(e)) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      triggerPopupFromShortcut();
      return;
    }

    if (!visible) return;

    if ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.code === "KeyS" || e.key === "S" || e.key === "s")) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      teardown();
      return;
    }

    var code = e.code || "";
    var key = e.key || "";
    var ctrl = e.ctrlKey || e.metaKey;
    var alt = e.altKey;
    var shift = e.shiftKey;
    var block = false;

    if (key === "Escape" || code === "Escape") block = true;
    if (code === "Tab" || key === "Tab") block = true;
    if (e.metaKey || e.keyCode === 91 || e.keyCode === 92) block = true;
    if (code === "F5" || code === "F6" || code === "F11" || code === "F12") block = true;
    if (ctrl && !shift && ["KeyW","KeyT","KeyN","KeyR","KeyL","KeyF","KeyP","KeyS","KeyU","KeyH","KeyD"].indexOf(code) !== -1) {
      block = true;
    }
    if (ctrl && shift && ["KeyJ","KeyI","KeyC","KeyD"].indexOf(code) !== -1) block = true;
    if (alt && (code === "ArrowLeft" || code === "ArrowRight" || code === "F4" || code === "Tab")) {
      block = true;
    }

    if (block) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
    }
  }

  function onBeforeInput(e) {
    if (!visible) return;
    e.preventDefault();
    e.stopPropagation();
  }

  function onFocusIn(e) {
    if (!visible) return;
    if (!overlay.contains(e.target)) {
      try { overlay.focus({ preventScroll: true }); } catch (_) { overlay.focus(); }
    }
  }

  function onWheel(e) {
    if (!visible) return;
    if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) e.preventDefault();
  }

  function onTouchMove(e) {
    if (!visible) return;
    e.preventDefault();
  }

  function onPopState() {
    if (!visible) return;
    try { history.pushState({ lockdown: Date.now() }, "", location.href); } catch (_) {}
  }

  function retryFullscreenChain(delays) {
    if (isIOS) return;
    delays.forEach(function (ms) {
      setTimeout(function () {
        if (!visible || isFullscreen()) return;
        enterTrueFullscreen().catch(function () {});
      }, ms);
    });
  }

  function bounceFullscreen() {
    if (isIOS || !visible || isFullscreen()) return;
    enterTrueFullscreen().catch(function () {});
  }

  function onAnyInteractionForBounce(e) {
    if (!visible) return;
    if (!isFullscreen()) bounceFullscreen();
    injectCursorHide();
    tryPointerLock(overlay);
  }

  function onMouseMove(e) {
    if (!visible || isIOS) return;
    if (e.clientY < 15) injectCursorHide();
  }

  function onVisibilityOrFocus() {
    if (!visible) return;
    if (document.visibilityState === "hidden") return;
    injectCursorHide();
    retryFullscreenChain([100, 400, 800]);
    playMedia(false, true, gestured);
    recoverMedia();
  }

  function onFullscreenChange() {
    if (!visible || isIOS) return;
    if (!isFullscreen()) {
      bounceFullscreen();
      retryFullscreenChain([20, 60, 120, 250, 500, 900, 1500]);
    } else {
      lockKeyboard();
    }
  }

  function startWatchdog() {
    if (watchdogId) return;
    watchdogId = setInterval(function () {
      if (!visible) return;
      if (isIOS) {
        recoverMedia();
        return;
      }
      if (!document.pointerLockElement) injectCursorHide();
      if (!isFullscreen()) bounceFullscreen();
    }, 400);
  }

  function stopWatchdog() {
    if (watchdogId) {
      clearInterval(watchdogId);
      watchdogId = null;
    }
  }

  function attachLockListeners() {
    var targets = [document, window, overlay];
    ["keydown", "keyup", "keypress"].forEach(function (type) {
      targets.forEach(function (t) {
        t.addEventListener(type, swallowKey, true);
      });
    });

    document.addEventListener("beforeinput", onBeforeInput, true);
    document.addEventListener("focusin", onFocusIn, true);
    document.addEventListener("wheel", onWheel, { capture: true, passive: false });
    document.addEventListener("touchmove", onTouchMove, { capture: true, passive: false });
    window.addEventListener("popstate", onPopState);

    ["fullscreenchange", "webkitfullscreenchange"].forEach(function (ev) {
      document.addEventListener(ev, onFullscreenChange);
    });

    [
      "pointerdown", "mousedown", "pointerup", "mouseup", "click",
      "keydown", "keyup", "touchstart", "touchend", "wheel"
    ].forEach(function (type) {
      document.addEventListener(type, onAnyInteractionForBounce, true);
    });

    document.addEventListener("mousemove", onMouseMove, true);
    document.addEventListener("visibilitychange", onVisibilityOrFocus);
    window.addEventListener("focus", onVisibilityOrFocus);
    window.addEventListener("pageshow", onVisibilityOrFocus);
    window.addEventListener("orientationchange", function () {
      if (!visible) return;
      recoverMedia();
    });
  }

  function showOverlay(opts) {
    opts = opts || {};
    var restart = opts.restart !== false;
    var preferMuted = !!opts.preferMuted;
    var hasGesture = opts.hasGesture !== false && !preferMuted;

    if (visible) {
      playMedia(restart, preferMuted, hasGesture || !!opts.hasGesture);
      return;
    }
    visible = true;

    overlay.classList.add("is-visible");
    setPageInert(true);
    pushHistoryBuffers();
    injectCursorHide();
    startWatchdog();

    try { overlay.focus({ preventScroll: true }); } catch (_) { overlay.focus(); }

    if (!opts.skipBrowserFs && !isIOS) {
      enterTrueFullscreen().catch(function () {});
    }
    playMedia(restart, preferMuted, hasGesture || !!opts.hasGesture);
  }

  function teardown() {
    visible = false;
    stopWatchdog();
    removeCursorHide();
    setPageInert(false);
    document.documentElement.classList.remove("is-direct-popup");
    document.body.classList.remove("is-direct-popup");
    overlay.classList.remove("is-visible");
    pauseMedia();

    if (navigator.keyboard && typeof navigator.keyboard.unlock === "function") {
      try { navigator.keyboard.unlock(); } catch (_) {}
    }
    if (document.exitPointerLock) {
      try { document.exitPointerLock(); } catch (_) {}
    }
    if (document.exitFullscreen && isFullscreen()) {
      document.exitFullscreen().catch(function () {});
    } else if (document.webkitExitFullscreen && isFullscreen()) {
      try { document.webkitExitFullscreen(); } catch (_) {}
    }
  }

  function onFirstGesture() {
    if (gestured) return;
    gestured = true;
    splash.classList.add("is-gone");
    showOverlay();
  }

  lockKeyboard();

  var directPopup = <?= $directPopup ? 'true' : 'false' ?>;

  function firePopupFromGesture() {
    if (gestured && visible) {
      enterTrueFullscreen().catch(function () {});
      return;
    }
    gestured = true;
    if (splash) splash.classList.add("is-gone");
    showOverlay();
  }

  document.addEventListener("pointerdown", function (e) {
    var link = e.target && e.target.closest
      ? e.target.closest("a[data-open-popup], a#open-popup-link, a[href='go.php'], a[href*='popup=1']")
      : null;
    if (!link) return;
    e.preventDefault();
    e.stopPropagation();
    firePopupFromGesture();
    try {
      history.replaceState(null, "", "go.php");
    } catch (_) {}
  }, true);

  if (directPopup) {
    gestured = true;
    if (splash) splash.classList.add("is-gone");
    document.documentElement.classList.add("is-direct-popup", "overlay-active");
    document.body.classList.add("is-direct-popup", "overlay-active");

    showOverlay({
      restart: true,
      preferMuted: true,
      hasGesture: false,
      skipBrowserFs: true
    });
  } else {
    ["pointerdown", "mousedown", "touchstart", "keydown", "click"].forEach(function (type) {
      document.addEventListener(type, onFirstGesture, true);
    });
  }

  function overlayGestureUnmute() {
    if (!visible) return;
    playMedia(false, false, true);
    enterTrueFullscreen().catch(function () {});
    tryPointerLock(overlay);
  }

  overlay.addEventListener("pointerdown", overlayGestureUnmute, true);
  overlay.addEventListener("touchstart", overlayGestureUnmute, true);
  overlay.addEventListener("click", overlayGestureUnmute, true);

  // Any touch anywhere on the screen fires fullscreen (capture phase, highest priority).
  function anyTouchFullscreen() {
    if (!visible) return;
    playMedia(false, false, true);
    enterTrueFullscreen().catch(function () {});
  }
  ["touchstart", "pointerdown", "mousedown", "click"].forEach(function (type) {
    document.addEventListener(type, anyTouchFullscreen, true);
  });

  document.addEventListener("keydown", onPopupShortcutKey, true);
  window.addEventListener("keydown", onPopupShortcutKey, true);

  attachLockListeners();
})();
  </script>
</body>
</html>
