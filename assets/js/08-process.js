/* =========================================================
   LINDO — 08 Process : 固定ステージ・スクロールテリング（ESモジュール / CSP 'self'）

   方針: 縦の素スクロールは“しないように見える” = ステージを ScrollTrigger pin し、
   スクロール量で scrub したマスタータイムラインで物語（制作プロセス）を進める。
   - デスクトップ(≥1024): pin＋scrub。中央フレームを three.js でディゾルブ（失敗時CSSクロスフェード）、
     章コピーのクロスフェード、進捗レール、背景色アーク。
   - SP/タブレット(<1024): pinしない。章を縦積み＋入場リビール（CSS既定＝stacked）。WebGL無効。
   - reduced-motion / gsap未ロード: 何もせず静的（CSS既定の縦積みで全可視）。
   ========================================================= */

const gsap = window.gsap;
const ScrollTrigger = window.ScrollTrigger;
const root = document.documentElement;

const reduced = matchMedia("(prefers-reduced-motion: reduce)").matches;
const IMAGES = [
  "/assets/process/00.jpg", "/assets/process/01.jpg", "/assets/process/02.jpg",
  "/assets/process/03.jpg", "/assets/process/04.jpg", "/assets/process/05.jpg",
];
const CH = IMAGES.length; // 章数（ステージ内）

/* ---- ブート（gsap無し/reduced は CSS既定の縦積みのまま＝何もしない） ---- */
if (gsap && ScrollTrigger && !reduced) {
  gsap.registerPlugin(ScrollTrigger);
  ScrollTrigger.config({ ignoreMobileResize: true });
  const ready = document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve();
  Promise.race([ready, new Promise((r) => setTimeout(r, 1500))]).then(() => preloadThenInit());
}

function preloadThenInit() {
  // 画像preload → CLS/ちらつき抑制 → refresh
  let left = IMAGES.length;
  const done = () => { if (--left <= 0) init(); };
  IMAGES.forEach((src) => { const im = new Image(); im.onload = done; im.onerror = done; im.src = src; });
  // 念のためのタイムアウト
  setTimeout(() => { if (left > 0) { left = 0; init(); } }, 2000);
}

function init() {
  const mm = gsap.matchMedia();
  mm.add("(min-width: 1024px)", () => setupDesktop());
  mm.add("(max-width: 1023px)", () => setupMobile());
  // フィナーレは両モードで入場リビール
  setupFinaleReveal();
  let rt;
  window.addEventListener("resize", () => { clearTimeout(rt); rt = setTimeout(() => ScrollTrigger.refresh(), 200); });
}

/* ============================ デスクトップ：pin＋scrub ============================ */
function setupDesktop() {
  const stage = document.querySelector("[data-stage]");
  const chapters = gsap.utils.toArray(".chapter");
  const rail = gsap.utils.toArray("[data-rail] li");
  const fallbackImgs = gsap.utils.toArray("[data-fimg]");
  if (!stage || !chapters.length) return;

  root.classList.add("fx"); // CSS: stacked → 固定オーバーレイ表示へ

  let cleanupGL = null;
  try {
    const lerpHex = makeLerp();
    const renderGL = initFrameGL(); // WebGL（失敗時 null）
    cleanupGL = renderGL ? renderGL.dispose : null;

    const setScene = (progress) => {
      const scene = progress * (CH - 1);
      const i = Math.min(CH - 1, Math.floor(scene));
      const f = scene - i;
      // 章コピー：距離でクロスフェード
      chapters.forEach((c, k) => {
        const op = Math.max(0, 1 - Math.abs(scene - k) * 1.5);
        c.style.opacity = op;
        c.style.transform = `translateY(${(k - scene) * 26}px)`;
        c.style.pointerEvents = op > 0.6 ? "auto" : "none";
      });
      // レール
      const active = Math.round(scene);
      rail.forEach((r, k) => r.classList.toggle("on", k === active));
      // 背景色アーク（olive → 温色 → 紫みピンク）
      stage.style.backgroundColor = lerpHex(progress);
      // フレーム
      if (renderGL) renderGL.update(i, Math.min(CH - 1, i + 1), f);
      else fallbackImgs.forEach((im, k) => { im.style.opacity = Math.max(0, 1 - Math.abs(scene - k) * 1.5); });
    };

    setScene(0); // 初期状態（ch0のみ表示）でフラッシュ防止

    const st = ScrollTrigger.create({
      trigger: stage,
      start: "top top",
      end: () => "+=" + (CH - 1) * window.innerHeight,
      scrub: 1,
      pin: true,
      anticipatePin: 1,
      invalidateOnRefresh: true,
      onUpdate: (self) => setScene(self.progress),
    });

    return () => { st.kill(); if (cleanupGL) cleanupGL(); root.classList.remove("fx"); };
  } catch (e) {
    // 失敗時は固定オーバーレイをやめて素の縦積みへ（壊さない）
    root.classList.remove("fx");
    if (cleanupGL) cleanupGL();
    return () => {};
  }
}

/* ============================ モバイル：縦積み＋入場リビール ============================ */
function setupMobile() {
  const items = gsap.utils.toArray(".chapter");
  const tweens = items.map((el) =>
    gsap.from(el.querySelectorAll("[data-rev]"), {
      y: 30, opacity: 0, duration: 0.9, ease: "power3.out", stagger: 0.08,
      scrollTrigger: { trigger: el, start: "top 80%", once: true },
    })
  );
  return () => tweens.forEach((t) => t.scrollTrigger && t.scrollTrigger.kill());
}

function setupFinaleReveal() {
  const fin = document.querySelector(".finale");
  if (!fin) return;
  gsap.from(fin.querySelectorAll("[data-rev]"), {
    y: 34, opacity: 0, duration: 0.9, ease: "power3.out", stagger: 0.08,
    scrollTrigger: { trigger: fin, start: "top 80%", once: true },
  });
}

/* ============================ 背景色アーク ============================ */
function makeLerp() {
  const stops = [
    [0.0, [28, 27, 14]],   // #1c1b0e olive
    [0.5, [38, 28, 20]],   // 温色
    [1.0, [42, 24, 34]],   // 紫みピンク寄り
  ];
  const mix = (a, b, t) => Math.round(a + (b - a) * t);
  return (p) => {
    let s = stops[0], e = stops[stops.length - 1];
    for (let k = 0; k < stops.length - 1; k++) {
      if (p >= stops[k][0] && p <= stops[k + 1][0]) { s = stops[k]; e = stops[k + 1]; break; }
    }
    const t = (p - s[0]) / (e[0] - s[0] || 1);
    const c = [0, 1, 2].map((j) => mix(s[1][j], e[1][j], t));
    return `rgb(${c[0]},${c[1]},${c[2]})`;
  };
}

/* ============================ 中央フレーム WebGL（three 動的import・失敗許容） ============================ */
function webglSupported() {
  try { const c = document.createElement("canvas"); return !!(window.WebGLRenderingContext && (c.getContext("webgl") || c.getContext("experimental-webgl"))); }
  catch (e) { return false; }
}

function initFrameGL() {
  const canvas = document.querySelector("[data-frame-canvas]");
  if (!canvas || !webglSupported()) return null;

  let api = null;
  // 同期APIを返しつつ、three は非同期ロード（ロード前は CSS フォールバックが見える）
  const state = { ready: false, uMix: null, uA: null, uB: null, renderer: null, scene: null, cam: null, tex: [], pending: null };

  import("/assets/vendor/three.module.min.js").then((THREE) => {
    try {
      const renderer = new THREE.WebGLRenderer({ canvas, antialias: false, alpha: true, powerPreference: "high-performance" });
      renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
      const scene = new THREE.Scene();
      const cam = new THREE.OrthographicCamera(-1, 1, 1, -1, 0, 1);
      const loader = new THREE.TextureLoader();
      const tex = IMAGES.map((src) => { const t = loader.load(src); t.colorSpace = THREE.SRGBColorSpace; t.minFilter = THREE.LinearFilter; return t; });

      const uniforms = {
        uTexA: { value: tex[0] }, uTexB: { value: tex[1] || tex[0] }, uMix: { value: 0 },
        uAmt: { value: 0.16 },
      };
      const frag = `
        precision highp float;
        uniform sampler2D uTexA; uniform sampler2D uTexB; uniform float uMix; uniform float uAmt;
        varying vec2 vUv;
        void main(){
          float m = smoothstep(0.0,1.0,uMix);
          vec2 dir = vec2(0.0, 1.0);
          vec4 a = texture2D(uTexA, vUv + dir * (uAmt * m));
          vec4 b = texture2D(uTexB, vUv - dir * (uAmt * (1.0 - m)));
          gl_FragColor = mix(a, b, m);
        }`;
      const vert = `varying vec2 vUv; void main(){ vUv = uv; gl_Position = vec4(position, 1.0); }`;
      const mat = new THREE.ShaderMaterial({ uniforms, fragmentShader: frag, vertexShader: vert });
      const quad = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), mat);
      scene.add(quad);

      const resize = () => {
        const w = canvas.clientWidth || 1, h = canvas.clientHeight || 1;
        renderer.setSize(w, h, false);
      };
      resize();
      window.addEventListener("resize", resize);

      state.ready = true; state.renderer = renderer; state.scene = scene; state.cam = cam;
      state.uniforms = uniforms; state.tex = tex; state.resize = resize; state.mat = mat; state.quad = quad;
      canvas.classList.add("is-on");
      if (state.pending) { api.update(state.pending.a, state.pending.b, state.pending.f); }
      else { renderer.render(scene, cam); }
    } catch (e) { /* three初期化失敗 → CSSフォールバックのまま */ }
  }).catch(() => { /* import失敗 → CSSフォールバックのまま */ });

  api = {
    update(a, b, f) {
      if (!state.ready) { state.pending = { a, b, f }; return; }
      state.uniforms.uTexA.value = state.tex[a];
      state.uniforms.uTexB.value = state.tex[b];
      state.uniforms.uMix.value = f;
      state.renderer.render(state.scene, state.cam);
    },
    dispose() {
      if (!state.ready) return;
      window.removeEventListener("resize", state.resize);
      state.mat.dispose(); state.quad.geometry.dispose();
      state.tex.forEach((t) => t.dispose()); state.renderer.dispose();
      canvas.classList.remove("is-on");
    },
  };
  return api;
}
