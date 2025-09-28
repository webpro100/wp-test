import '../scss/main.scss';

import { gsap } from 'gsap';
import ScrollTrigger from 'gsap/ScrollTrigger';
import Lenis from 'lenis';

gsap.registerPlugin(ScrollTrigger);


document.addEventListener('DOMContentLoaded', async () => {




  // Lenis
  const lenis = new Lenis();
  function raf(t){ lenis.raf(t); requestAnimationFrame(raf); }
  requestAnimationFrame(raf);
  lenis.on('scroll', ScrollTrigger.update);
  
//   requestAnimationFrame(() => ScrollTrigger.refresh());
});
