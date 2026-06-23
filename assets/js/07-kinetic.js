/* =========================================================
   LINDO — 07 Kinetic : モーション統括（ESモジュール / CSP script-src 'self'）

   主軸: GSAP (ScrollTrigger / SplitText) + Lenis
   要所: three.js（ヒーローWebGL背景・作品ホバー歪み）= 動的import・失敗許容
   方針: 壊れない。gsap未ロード/縮小モーション/タッチ/WebGL非対応は静的に縮退。
   ========================================================= */

const gsap = window.gsap;
const ScrollTrigger = window.ScrollTrigger;
const SplitText = window.SplitText;
const Lenis = window.Lenis;

const mqReduce = matchMedia("(prefers-reduced-motion: reduce)");
const reduced = mqReduce.matches;
const finePointer = matchMedia("(hover: hover) and (pointer: fine)").matches;
const root = document.documentElement;

/* ---- フォールバック: gsap が無い or reduced のときは即・全表示 ---- */
function showAllStatic() {
  root.classList.add("no-motion");
}

function boot() {
  gsap.registerPlugin(ScrollTrigger);
  if (SplitText) gsap.registerPlugin(SplitText);
  ScrollTrigger.config({ ignoreMobileResize: true });

  if (reduced) {
    // 動きなし：内容は CSS 既定で可視。Lenis/WebGL/pin は一切初期化しない。
    showAllStatic();
    return;
  }
  // SplitText は webfont 確定後に実行（行分割の誤差・FOUT対策）。最大1.5sでタイムアウト。
  const ready = document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve();
  Promise.race([ready, new Promise((r) => setTimeout(r, 1500))]).then(() => initMotion());
}

if (!gsap || !ScrollTrigger) {
  // ライブラリ未ロード → 何もしない（CSSの no-motion 既定で全要素可視）
  showAllStatic();
} else {
  boot();
}

/* ============================ 本体 ============================ */
function initMotion() {
  /* ---------- Lenis（タッチはネイティブ＝smoothTouch無効） ---------- */
  let lenis = null;
  if (Lenis) {
    lenis = new Lenis({ lerp: 0.1, smoothWheel: true });
    lenis.on("scroll", ScrollTrigger.update);
    gsap.ticker.add((t) => lenis.raf(t * 1000));
    gsap.ticker.lagSmoothing(0);
  }

  // アンカーリンクのスムーズスクロール
  document.querySelectorAll('a[href^="#"]').forEach((a) => {
    a.addEventListener("click", (e) => {
      const id = a.getAttribute("href");
      if (id.length < 2) return;
      const target = document.querySelector(id);
      if (!target) return;
      e.preventDefault();
      if (lenis) lenis.scrollTo(target, { offset: -60 });
      else target.scrollIntoView({ behavior: "smooth" });
    });
  });

  /* ---------- プリローダー ---------- */
  const pre = document.querySelector("[data-preloader]");
  if (pre) {
    const num = pre.querySelector("[data-pre-num]");
    const obj = { v: 0 };
    const tl = gsap.timeline();
    tl.to(obj, {
      v: 100, duration: 1.1, ease: "power2.inOut",
      onUpdate: () => { if (num) num.textContent = Math.round(obj.v).toString().padStart(3, "0"); },
    })
      .to(pre, { yPercent: -100, duration: 0.9, ease: "power4.inOut" }, "+=0.15")
      .set(pre, { display: "none" })
      .add(() => playHero());
    if (lenis) { lenis.stop(); tl.eventCallback("onComplete", () => lenis.start()); }
  } else {
    playHero();
  }

  /* ---------- ヒーロー見出しのリビール ---------- */
  let heroSplit = null;
  function playHero() {
    const tl = gsap.timeline({ defaults: { ease: "power4.out" } });
    const eyebrow = document.querySelector("[data-hero-eyebrow]");
    const sub = document.querySelector("[data-hero-sub]");
    const head = document.querySelector("[data-hero-head]");

    if (eyebrow) tl.from(eyebrow, { yPercent: 120, opacity: 0, duration: 0.8 });
    if (head) {
      if (SplitText) {
        heroSplit = new SplitText(head, { type: "lines,chars", linesClass: "ln" });
        tl.from(heroSplit.chars, { yPercent: 110, opacity: 0, duration: 0.9, stagger: 0.018 }, "-=0.4");
      } else {
        tl.from(head, { yPercent: 30, opacity: 0, duration: 1 }, "-=0.4");
      }
    }
    if (sub) tl.from(sub, { y: 24, opacity: 0, duration: 0.8 }, "-=0.5");
    const cue = document.querySelector("[data-hero-cue]");
    if (cue) tl.from(cue, { opacity: 0, duration: 0.6 }, "-=0.2");
  }

  /* ---------- スクロール・リビール（共通：デスクトップ/モバイル両方） ---------- */
  // 行リビール（SplitText）
  document.querySelectorAll("[data-split]").forEach((el) => {
    const make = () => {
      let targets = [el];
      let split = null;
      if (SplitText) { split = new SplitText(el, { type: "lines", linesClass: "ln" }); targets = split.lines; }
      gsap.from(targets, {
        yPercent: 110, opacity: 0, duration: 0.9, ease: "power4.out", stagger: 0.08,
        scrollTrigger: { trigger: el, start: "top 85%", once: true },
      });
    };
    make();
  });
  // フェードアップ
  gsap.utils.toArray("[data-fade]").forEach((el) => {
    gsap.from(el, {
      y: 40, opacity: 0, duration: 0.9, ease: "power3.out",
      scrollTrigger: { trigger: el, start: "top 88%", once: true },
    });
  });

  /* ---------- キネティック・ティッカーの速度スキュー（デスクトップのみ） ---------- */
  const mm = gsap.matchMedia();
  mm.add("(min-width: 1024px)", () => {
    const tracks = gsap.utils.toArray("[data-ticker-skew]");
    if (tracks.length) {
      const setters = tracks.map((t) => gsap.quickTo(t, "skewX", { duration: 0.4, ease: "power3" }));
      const st = ScrollTrigger.create({
        onUpdate: (self) => {
          const v = gsap.utils.clamp(-12, 12, self.getVelocity() / -260);
          setters.forEach((s) => s(v));
        },
      });
      return () => st.kill();
    }
  });

  /* ---------- Works：横スクロール・ピン（デスクトップのみ。SPはCSSのネイティブ横スクロール） ---------- */
  mm.add("(min-width: 1024px)", () => {
    const section = document.querySelector("[data-works]");
    const track = document.querySelector("[data-works-track]");
    if (!section || !track) return;
    const getLen = () => Math.max(0, track.scrollWidth - window.innerWidth + 80);
    const tween = gsap.to(track, {
      x: () => -getLen(), ease: "none",
      scrollTrigger: {
        trigger: section, start: "top top", end: () => "+=" + getLen(),
        scrub: 1, pin: true, anticipatePin: 1, invalidateOnRefresh: true,
      },
    });
    return () => tween.scrollTrigger && tween.scrollTrigger.kill();
  });

  /* ---------- マグネティックボタン＆カスタムカーソル（fine pointer のみ） ---------- */
  if (finePointer) {
    initMagnetic();
    initCursor();
  }

  /* ---------- ヒーロー WebGL（デスクトップ＋fine＋WebGL対応時のみ・動的import・失敗許容） ---------- */
  mm.add("(min-width: 1024px)", () => {
    let cleanup = null;
    if (finePointer && webglSupported()) {
      initHeroWebGL().then((c) => { cleanup = c; }).catch(() => {});
    }
    return () => { if (cleanup) cleanup(); };
  });

  // リサイズで ScrollTrigger を更新（debounce）
  let rt;
  window.addEventListener("resize", () => {
    clearTimeout(rt);
    rt = setTimeout(() => ScrollTrigger.refresh(), 200);
  });
}

/* ============================ マグネティック ============================ */
function initMagnetic() {
  document.querySelectorAll("[data-magnetic]").forEach((btn) => {
    const xTo = gsap.quickTo(btn, "x", { duration: 0.4, ease: "power3" });
    const yTo = gsap.quickTo(btn, "y", { duration: 0.4, ease: "power3" });
    btn.addEventListener("mousemove", (e) => {
      const r = btn.getBoundingClientRect();
      xTo((e.clientX - (r.left + r.width / 2)) * 0.35);
      yTo((e.clientY - (r.top + r.height / 2)) * 0.35);
    });
    btn.addEventListener("mouseleave", () => { xTo(0); yTo(0); });
  });
}

/* ============================ カスタムカーソル ============================ */
function initCursor() {
  const cur = document.querySelector("[data-cursor]");
  if (!cur) return;
  gsap.set(cur, { xPercent: -50, yPercent: -50 });
  const xTo = gsap.quickTo(cur, "x", { duration: 0.25, ease: "power3" });
  const yTo = gsap.quickTo(cur, "y", { duration: 0.25, ease: "power3" });
  window.addEventListener("mousemove", (e) => { xTo(e.clientX); yTo(e.clientY); });
  document.querySelectorAll("a, button, [data-cursor-hover]").forEach((el) => {
    el.addEventListener("mouseenter", () => cur.classList.add("is-hover"));
    el.addEventListener("mouseleave", () => cur.classList.remove("is-hover"));
  });
  root.classList.add("has-cursor");
}

/* ============================ WebGL 判定 ============================ */
function webglSupported() {
  try {
    const c = document.createElement("canvas");
    return !!(window.WebGLRenderingContext && (c.getContext("webgl") || c.getContext("experimental-webgl")));
  } catch (e) { return false; }
}

/* ============================ ヒーロー WebGL（three.js 動的import） ============================ */
async function initHeroWebGL() {
  const canvas = document.querySelector("[data-hero-canvas]");
  if (!canvas) return null;
  const THREE = await import("/assets/vendor/three.module.min.js");

  const renderer = new THREE.WebGLRenderer({ canvas, antialias: false, alpha: true, powerPreference: "high-performance" });
  const dpr = Math.min(window.devicePixelRatio || 1, 1.5);
  renderer.setPixelRatio(dpr);

  const scene = new THREE.Scene();
  const camera = new THREE.OrthographicCamera(-1, 1, 1, -1, 0, 1);

  const uniforms = {
    u_time: { value: 0 },
    u_res: { value: new THREE.Vector2(1, 1) },
    u_mouse: { value: new THREE.Vector2(0.5, 0.5) },
    u_olive: { value: new THREE.Color(0x22210f) },
    u_olive2: { value: new THREE.Color(0x3c3a20) },
    u_pink: { value: new THREE.Color(0xe79cb0) },
    u_gold: { value: new THREE.Color(0xb9a86b) },
  };

  const frag = `
    precision highp float;
    uniform float u_time; uniform vec2 u_res; uniform vec2 u_mouse;
    uniform vec3 u_olive; uniform vec3 u_olive2; uniform vec3 u_pink; uniform vec3 u_gold;
    // 2D value noise + fbm
    float hash(vec2 p){ return fract(sin(dot(p, vec2(127.1,311.7)))*43758.5453); }
    float noise(vec2 p){ vec2 i=floor(p), f=fract(p); vec2 u=f*f*(3.0-2.0*f);
      return mix(mix(hash(i+vec2(0,0)),hash(i+vec2(1,0)),u.x),
                 mix(hash(i+vec2(0,1)),hash(i+vec2(1,1)),u.x),u.y); }
    float fbm(vec2 p){ float v=0.0,a=0.5; for(int i=0;i<5;i++){ v+=a*noise(p); p*=2.0; a*=0.5; } return v; }
    void main(){
      vec2 uv = gl_FragCoord.xy / u_res.xy;
      vec2 asp = vec2(u_res.x/u_res.y, 1.0);
      vec2 p = uv*asp;
      float t = u_time*0.04;
      vec2 q = vec2(fbm(p*1.6 + t), fbm(p*1.6 - t + 5.2));
      float n = fbm(p*2.2 + q*1.5 + vec2(0.0, t*1.4));
      // マウス方向に淡くグロー
      float md = distance(uv, u_mouse);
      float glow = smoothstep(0.55, 0.0, md) * 0.5;
      vec3 col = mix(u_olive, u_olive2, smoothstep(0.2,0.8,n));
      col = mix(col, u_pink, smoothstep(0.62,0.95,n)*0.55 + glow*0.45);
      col = mix(col, u_gold, smoothstep(0.85,1.0,n)*0.12);
      // ビネット＋粒状
      col *= 0.86 + 0.14*smoothstep(1.1,0.2,distance(uv,vec2(0.5)));
      col += (hash(uv*u_time)-0.5)*0.025;
      gl_FragColor = vec4(col, 1.0);
    }`;
  const vert = `void main(){ gl_Position = vec4(position, 1.0); }`;

  const mat = new THREE.ShaderMaterial({ uniforms, fragmentShader: frag, vertexShader: vert });
  const quad = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), mat);
  scene.add(quad);

  function resize() {
    const w = canvas.clientWidth || window.innerWidth;
    const h = canvas.clientHeight || window.innerHeight;
    renderer.setSize(w, h, false);
    uniforms.u_res.value.set(w * dpr, h * dpr);
  }
  resize();
  window.addEventListener("resize", resize);

  // マウス（ヒーロー内のみ）
  const onMove = (e) => {
    const r = canvas.getBoundingClientRect();
    uniforms.u_mouse.value.set((e.clientX - r.left) / r.width, 1.0 - (e.clientY - r.top) / r.height);
  };
  window.addEventListener("mousemove", onMove);

  // 画面外では停止（負荷・電池対策）
  let visible = true;
  const io = new IntersectionObserver((es) => { visible = es[0].isIntersecting; }, { threshold: 0.01 });
  io.observe(canvas);

  let raf, last = performance.now();
  const loop = (now) => {
    raf = requestAnimationFrame(loop);
    if (!visible) return;
    uniforms.u_time.value += (now - last) / 1000;
    last = now;
    renderer.render(scene, camera);
  };
  raf = requestAnimationFrame(loop);
  canvas.classList.add("is-on"); // CSSグラデ→WebGLへフェード

  // cleanup（matchMedia がブレークポイント外で呼ぶ）
  return () => {
    cancelAnimationFrame(raf);
    io.disconnect();
    window.removeEventListener("resize", resize);
    window.removeEventListener("mousemove", onMove);
    mat.dispose(); quad.geometry.dispose(); renderer.dispose();
    canvas.classList.remove("is-on");
  };
}
