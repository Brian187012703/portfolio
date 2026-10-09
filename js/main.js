/**
 * Futuristic Graphic Designer Portfolio - Interactive Engine
 * Designer: Rayhan Aditya
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Initialize Ambient Canvas Particles
  initAmbientCanvas();

  // 2. Custom Magnetic Cursor
  initCustomCursor();

  // 3. Stats Counter Animation
  initStatsCounters();

  // 4. 3D Tilt Effect on Hero Portrait & Cards
  initTiltEffects();

  // 5. Project Filtering & Case Studies Modal
  initProjectsAndModal();

  // 6. Interactive Contact System & Copy to Clipboard
  initContactSystem();

  // 7. Web Audio API Futuristic Sound Engine
  initSoundEngine();

  // 8. Back to Top Button
  initBackToTop();
});

/* ==========================================================================
   1. AMBIENT CANVAS PARTICLES
   ========================================================================== */
function initAmbientCanvas() {
  const canvas = document.getElementById('ambient-canvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  let width = (canvas.width = window.innerWidth);
  let height = (canvas.height = window.innerHeight);

  window.addEventListener('resize', () => {
    width = canvas.width = window.innerWidth;
    height = canvas.height = window.innerHeight;
  });

  const particles = [];
  const particleCount = Math.min(65, Math.floor(width / 22));

  class Particle {
    constructor() {
      this.reset();
    }
    reset() {
      this.x = Math.random() * width;
      this.y = Math.random() * height;
      this.size = Math.random() * 2 + 0.8;
      this.speedX = (Math.random() - 0.5) * 0.4;
      this.speedY = (Math.random() - 0.5) * 0.4;
      this.color = Math.random() > 0.4 ? 'rgba(255, 30, 45,' : 'rgba(255, 255, 255,';
      this.alpha = Math.random() * 0.5 + 0.2;
      this.alphaChange = (Math.random() * 0.01 + 0.005) * (Math.random() > 0.5 ? 1 : -1);
    }
    update() {
      this.x += this.speedX;
      this.y += this.speedY;
      this.alpha += this.alphaChange;

      if (this.alpha <= 0.1 || this.alpha >= 0.7) {
        this.alphaChange *= -1;
      }

      if (this.x < 0) this.x = width;
      if (this.x > width) this.x = 0;
      if (this.y < 0) this.y = height;
      if (this.y > height) this.y = 0;
    }
    draw() {
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
      ctx.fillStyle = `${this.color} ${this.alpha})`;
      ctx.shadowBlur = 8;
      ctx.shadowColor = '#ff1e2d';
      ctx.fill();
    }
  }

  for (let i = 0; i < particleCount; i++) {
    particles.push(new Particle());
  }

  function animate() {
    ctx.clearRect(0, 0, width, height);

    // Draw subtle connecting lines
    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        const dx = particles[i].x - particles[j].x;
        const dy = particles[i].y - particles[j].y;
        const dist = Math.sqrt(dx * dx + dy * dy);

        if (dist < 110) {
          ctx.beginPath();
          ctx.moveTo(particles[i].x, particles[i].y);
          ctx.lineTo(particles[j].x, particles[j].y);
          ctx.strokeStyle = `rgba(255, 30, 45, ${0.12 * (1 - dist / 110)})`;
          ctx.lineWidth = 0.6;
          ctx.stroke();
        }
      }
    }

    particles.forEach((p) => {
      p.update();
      p.draw();
    });

    requestAnimationFrame(animate);
  }

  animate();
}

/* ==========================================================================
   2. CUSTOM MAGNETIC CURSOR
   ========================================================================== */
function initCustomCursor() {
  const dot = document.querySelector('.custom-cursor-dot');
  const ring = document.querySelector('.custom-cursor-ring');
  if (!dot || !ring) return;

  let mouseX = -100;
  let mouseY = -100;
  let ringX = -100;
  let ringY = -100;
  let hasMoved = false;

  dot.style.opacity = '0';
  ring.style.opacity = '0';

  window.addEventListener('mousemove', (e) => {
    mouseX = e.clientX;
    mouseY = e.clientY;
    if (!hasMoved) {
      hasMoved = true;
      ringX = mouseX;
      ringY = mouseY;
      dot.style.opacity = '1';
      ring.style.opacity = '1';
    }
    dot.style.transform = `translate(${mouseX}px, ${mouseY}px)`;
  });

  document.addEventListener('mouseleave', () => {
    dot.style.opacity = '0';
    ring.style.opacity = '0';
  });

  document.addEventListener('mouseenter', () => {
    if (hasMoved) {
      dot.style.opacity = '1';
      ring.style.opacity = '1';
    }
  });

  function renderRing() {
    if (hasMoved) {
      ringX += (mouseX - ringX) * 0.18;
      ringY += (mouseY - ringY) * 0.18;
      ring.style.transform = `translate(${ringX}px, ${ringY}px)`;
    }
    requestAnimationFrame(renderRing);
  }
  renderRing();

  const interactives = document.querySelectorAll('a, button, .project-card, .skill-pill, .contact-method-card, .filter-tab');
  interactives.forEach((el) => {
    el.addEventListener('mouseenter', () => {
      ring.classList.add('active');
    });
    el.addEventListener('mouseleave', () => {
      ring.classList.remove('active');
    });
  });
}

/* ==========================================================================
   3. STATS COUNTER ANIMATION
   ========================================================================== */
function initStatsCounters() {
  const statNumbers = document.querySelectorAll('.stat-number');
  if (!statNumbers.length) return;

  let hasAnimated = false;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting && !hasAnimated) {
          hasAnimated = true;
          statNumbers.forEach((item) => {
            const target = parseInt(item.getAttribute('data-target') || '0', 10);
            const prefix = item.getAttribute('data-prefix') || '';
            const suffix = item.getAttribute('data-suffix') || '+';
            animateValue(item, 0, target, 1600, prefix, suffix);
          });
        }
      });
    },
    { threshold: 0.3 }
  );

  const statsSection = document.querySelector('.hero-stats-group');
  if (statsSection) observer.observe(statsSection);

  function animateValue(element, start, end, duration, prefix, suffix) {
    let startTimestamp = null;
    const step = (timestamp) => {
      if (!startTimestamp) startTimestamp = timestamp;
      const progress = Math.min((timestamp - startTimestamp) / duration, 1);
      const easeProgress = 1 - Math.pow(1 - progress, 3); // ease-out cubic
      const current = Math.floor(easeProgress * (end - start) + start);
      element.textContent = `${prefix}${current}${suffix}`;
      if (progress < 1) {
        window.requestAnimationFrame(step);
      } else {
        element.textContent = `${prefix}${end}${suffix}`;
      }
    };
    window.requestAnimationFrame(step);
  }
}

/* ==========================================================================
   4. 3D TILT EFFECT
   ========================================================================== */
function initTiltEffects() {
  const portrait = document.querySelector('.portrait-container');
  if (portrait) {
    const wrapper = document.querySelector('.portrait-wrapper');
    wrapper.addEventListener('mousemove', (e) => {
      const rect = wrapper.getBoundingClientRect();
      const x = e.clientX - rect.left - rect.width / 2;
      const y = e.clientY - rect.top - rect.height / 2;
      const rotateX = (-y / (rect.height / 2)) * 8;
      const rotateY = (x / (rect.width / 2)) * 8;
      portrait.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
    });

    wrapper.addEventListener('mouseleave', () => {
      portrait.style.transform = 'rotateX(0deg) rotateY(0deg)';
    });
  }
}

/* ==========================================================================
   5. DYNAMIC PROJECTS ENGINE & CASE STUDY MODAL
   ========================================================================== */
<<<<<<< HEAD
let projectData = {
  '1': {
    number: '01',
    title: 'SOCIAL MEDIA POSTS',
    category: 'social',
    categoryLabel: 'Branding & Creative Content',
    client: 'Brands & Digital Creators',
    year: '2023 — 2026',
    role: 'Graphic Artist & Content Stylist',
    deliverables: 'Social Media Banners, Story Creatives, Promo Ads, Visual Guides',
    image: 'assets/images/project_veloce.jpg',
=======
const projectData = {
  'nba-next': {
    number: '01',
    title: "WHO'S GONNA BE NEXT?",
    category: 'Digital Art / Sports Poster',
    client: 'Concept Sports Poster Showcase',
    year: '2024 — 2026',
    role: 'Digital Concept Artist & Retoucher',
    deliverables: 'Cinematic Sports Poster, High-Res Composite, Social Promo Artwork',
    image: 'assets/images/digital_nba_next.jpg',
>>>>>>> origin/main
    description:
      'A powerful, cinematic sports concept poster spotlighting top basketball prodigies Kon Knueppel and Cooper Flagg standing atop mountain peaks looking towards the glowing Kia NBA Rookie of the Year trophy at sunrise. Crafted with atmospheric lighting, golden cloud depth, and bold editorial typography.',
    highlights: [
      'Designed realistic sunset lighting angles matching player cutout highlights.',
      'Created custom glass refraction and glowing radiance for the NBA Rookie trophy centerpiece.',
      'Authored with high-contrast typography and dynamic sports poster composition.'
    ],
    liveDemoUrl: '#'
  },
<<<<<<< HEAD
  '2': {
    number: '02',
    title: 'CUSTOM APPAREL',
    category: 'apparel',
    categoryLabel: 'Clothing & Streetwear Merch',
    client: 'Apparel Brands & Independent Merch',
    year: '2022 — 2026',
    role: 'Apparel Graphic Artist',
    deliverables: 'Vector T-Shirt Graphics, Streetwear Typography, Silk Screen Separations',
    image: 'assets/images/project_woodcraft.jpg',
=======
  'alex-eala': {
    number: '02',
    title: 'ALEX EALA: THE FACE OF THE PHILIPPINES',
    category: 'Digital Art / Cultural Sports Tribute',
    client: 'Filipino Pride Artwork Series',
    year: '2024 — 2026',
    role: 'Digital Artist & Creative Retoucher',
    deliverables: 'Editorial Portrait Poster, Cultural Art Tribute, High-Detail Matte Painting',
    image: 'assets/images/digital_alex_eala.jpg',
>>>>>>> origin/main
    description:
      'A majestic tribute celebrating tennis sensation Alex Eala as an ambassador of Philippine pride. Blends traditional Filipino heritage with elite sports athleticism, depicting Alex holding the Philippine national flag in an elegant Filipiniana gown across an endless golden wheat field at twilight.',
    highlights: [
      'Complex blending of fabric texture, golden hour ambiance, and realistic wind physics on the Philippine flag.',
      'Seamless digital composite of traditional Filipiniana dress and modern tennis racket equipment.',
      'Evocative atmospheric depth with soft cloud horizons and soaring birds.'
    ],
    liveDemoUrl: '#'
  },
<<<<<<< HEAD
  '3': {
    number: '03',
    title: 'DIGITAL ARTS',
    category: 'digital',
    categoryLabel: 'Concept Art & Digital Illustration',
    client: 'Commissions & Creative Studios',
    year: '2021 — 2026',
    role: 'Digital Illustrator & Concept Artist',
    deliverables: 'High-Res Digital Paintings, Character Designs, Concept Artworks',
    image: 'assets/images/project_urbanic.jpg',
=======
  'post-malone': {
    number: '03',
    title: 'POST MALONE: AUSTIN POST TRIBUTE',
    category: 'Digital Art / Pop Culture Montage',
    client: 'Music & Concert Series Artwork',
    year: '2024 — 2026',
    role: 'Graphic Stylist & Photo Manipulator',
    deliverables: 'Fiery Tour Poster, Monochromatic Screen Print Art, High-Impact Album Creative',
    image: 'assets/images/digital_post_malone.jpg',
>>>>>>> origin/main
    description:
      'An explosive, fiery monochromatic concert montage celebrating global music icon Post Malone. Features layered high-energy live performance captures, detailed facial expressions, body tattoos, blazing flame textures, and custom signature typography.',
    highlights: [
      'Multi-layered composite with seamless transition between live performance poses.',
      'Intense fiery color grade with burning embers, dust grains, and radiant fire highlights.',
      'Precision masking of facial features, beard details, and intricate body ink.'
    ],
    liveDemoUrl: '#'
  },
<<<<<<< HEAD
  '4': {
    number: '04',
    title: 'TRADITIONAL ARTS',
    category: 'traditional',
    categoryLabel: 'Drawing & Painting Studies',
    client: 'Private Art Collectors & Exhibitions',
    year: '2019 — 2026',
    role: 'Traditional Fine Artist',
    deliverables: 'Graphite Drawings, Ink Sketches, Canvas Paintings, Mixed Media',
    image: 'assets/images/project_neural.jpg',
=======
  'lewis-hamilton': {
    number: '04',
    title: 'LEWIS HAMILTON: FORMULA 1 CHAMPION',
    category: 'Digital Art / Motorsport Artwork',
    client: 'F1 Tribute & Racing Graphics',
    year: '2024 — 2026',
    role: 'Motorsport Graphic Designer',
    deliverables: 'F1 Grand Prix Poster, Race Winner Commemorative Art, High-Detail Car Composite',
    image: 'assets/images/digital_lewis_hamilton.jpg',
>>>>>>> origin/main
    description:
      'A gritty, high-contrast tribute celebrating 7-time Formula 1 World Champion Sir Lewis Hamilton. Combines neon purple chromatic helmet reflections, emotional championship victory moments, British flag elements, and the iconic Mercedes-AMG Petronas F1 race car.',
    highlights: [
      'Vibrant cyber-neon lighting reflections mapped accurately across the racing helmet visor.',
      'High-dynamic range composite integrating the F1 race car in motion with victorious athlete portraits.',
      'Textured halftone grit and Formula 1 typography layout.'
    ],
    liveDemoUrl: '#'
  },
  'brigida-friends': {
    number: '05',
    title: 'BRIGIDA & FRIENDS: MEADOW ADVENTURE',
    category: 'Digital Art / 3D Cartoon Composite',
    client: 'Creative Character & Photo Artwork',
    year: '2024 — 2026',
    role: 'Digital Illustrator & 3D Compositor',
    deliverables: 'Stylized Character Composite, Custom 3D Logo Typography, Whimsical Scene Art',
    image: 'assets/images/digital_brigida_minions.jpg',
    description:
      'A vibrant, playful photo composite merging real-life selfie perspective photography with animated 3D Minions characters, floating butterflies, a peeled banana foreground, and custom yellow 3D title lettering nestled in an expansive blooming green meadow.',
    highlights: [
      'Perspective matching between real-world foreground selfie arm and 3D character scale.',
      'Custom 3D extrusion and sunny lighting simulation on "BRIGIDA and Friends" logo.',
      'Vivid color treatment bringing out lush meadow greens, sunny skies, and playful yellow accents.'
    ],
    liveDemoUrl: '#'
  },
  'trad-lily-portrait': {
    number: '06',
    title: 'LILY IN BLOOM',
    category: 'Traditional Art / Graphite & Pencil Portrait',
    client: 'Original Sketchbook & Portfolio Study',
    year: '2023 — 2026',
    role: 'Traditional Fine Artist',
    deliverables: 'Graphite on Textured Paper, Botanical Illustration, Character Study',
    image: 'assets/images/traditional_lily_portrait.jpg',
    description:
      'An evocative graphite portrait executed on cold-press textured drawing paper depicting an elegant stylized muse adorned with delicate blooming lilies in her hair and cascading across the foreground. Signed "TANAEL" by the artist.',
    highlights: [
      'Rendered with varying graphite pencil grades (2B to 8B) for rich velvet black tones and soft skin highlights.',
      'Detailed botanical study of lily petal veins and organic folds.',
      'Mastery of expressive facial anatomy, delicate eyelash work, and fine hair strand details.'
    ],
    liveDemoUrl: '#'
  },
  'trad-female-study': {
    number: '07',
    title: 'REALISM PORTRAIT STUDY',
    category: 'Traditional Art / Charcoal & Graphite Drawing',
    client: 'Anatomy & Form Master Study',
    year: '2023 — 2026',
    role: 'Traditional Fine Artist',
    deliverables: 'Charcoal Shading Study, Blending & Smudge Technique, Kasimir Pencil Art',
    image: 'assets/images/traditional_female_study.jpg',
    description:
      'A masterclass in soft tonal gradients and charcoal blending, capturing the delicate planes of the human face, collarbone, and gaze. Crafted with professional Kasimir graphite and charcoal pencils.',
    highlights: [
      'Subtle charcoal blending using blending stumps and soft tissue to achieve smooth skin transitions.',
      'High-precision eye and lip rendering with lifelike specular light reflection.',
      'Demonstrates deep traditional understanding of three-dimensional form and diffuse lighting.'
    ],
    liveDemoUrl: '#'
  },
  'trad-jordan-sneaker': {
    number: '08',
    title: 'AIR JORDAN 1 HIGH RETRO',
    category: 'Traditional Art / Sneaker & Product Illustration',
    client: 'Streetwear & Sneaker Culture Study',
    year: '2023 — 2026',
    role: 'Traditional Fine Artist',
    deliverables: 'Charcoal Sneaker Illustration, Leather Texture Study, Tone Precision Art',
    image: 'assets/images/traditional_jordan_sneaker.jpg',
    description:
      'An impeccably rendered charcoal and graphite study of the legendary Air Jordan 1 High retro silhouette. Accurately details leather panel grain, stitch perforations on the toe box, lace tension, and rubber midsole contours.',
    highlights: [
      'Deep matte black charcoal application contrasting against crisp white negative paper space.',
      'Intricate leather texture replication and micro-stitch detailing along sneaker panels.',
      'Created with Faber-Castell Pitt Charcoal pencils and fine detail blending tools.'
    ],
    liveDemoUrl: '#'
  },
  'trad-masked-portrait': {
    number: '09',
    title: 'SILHOUETTE & SHADOW',
    category: 'Traditional Art / Charcoal Stippling & Shading',
    client: 'Urban Aesthetic Study',
    year: '2023 — 2026',
    role: 'Traditional Fine Artist',
    deliverables: 'Textured Paper Charcoal Art, Urban Streetwear Portrait, High-Contrast Study',
    image: 'assets/images/traditional_masked_portrait.jpg',
    description:
      'A contemporary urban portrait rendered with intense charcoal blacks on heavy textured paper. Showcases a youth in spectacles and mask under dramatic overhead lighting casting deep shadows.',
    highlights: [
      'Heavy black charcoal pigmentation creating bold contrast with crisp paper tooth texture.',
      'Dramatic top-down directional lighting study defining hair volume and glasses reflection.',
      'Exploration of modern urban youth identity through traditional fine art media.'
    ],
    liveDemoUrl: '#'
  },
  'trad-blindfold-figure': {
    number: '10',
    title: 'BOUND PERCEPTION',
    category: 'Traditional Art / Conceptual Graphite Study',
    client: 'Conceptual Fine Art Series',
    year: '2023 — 2026',
    role: 'Traditional Fine Artist',
    deliverables: 'Pencil on Paper, Fabric Drape & Fold Study, Conceptual Portraiture',
    image: 'assets/images/traditional_blindfold_figure.jpg',
    description:
      'A poignant conceptual artwork of a blindfolded figure in an unbuttoned overcoat and chain necklace. Focuses heavily on the physics of cloth draping, lapel folds, and soft skin rendering around the jaw and neck.',
    highlights: [
      'Extensive study of fabric folds, shadows, and textile weight across the coat lapel.',
      'Delicate shading using soft graphite grades and precision blending brushes.',
      'Symbolic narrative exploring introspection, vulnerability, and modern stoicism.'
    ],
    liveDemoUrl: '#'
  },
  'apparel-wasoy-mayon': {
    number: '11',
    title: 'WASOY: MAYON PEACE TEE',
    category: 'Custom Apparel / Streetwear Graphic Tee',
    client: 'Wasoy Concept Apparel 2025',
    year: '2024 — 2026',
    role: 'Apparel Graphic Designer & Typographer',
    deliverables: 'Vector Print Separations, Direct-to-Film (DTF) Layout, Streetwear Lookbook Mockup',
    image: 'assets/images/apparel_wasoy_mayon.jpg',
    description:
      'Clean, minimalist oversized streetwear tee concept featuring a picturesque brush-stroke vignette of Mount Mayon. Paired with bold serif branding and poignant typography: "Even the fire of Mayon can\'t silence a child\'s cry for peace."',
    highlights: [
      'Custom brush-edge watercolor vignette framing Mount Mayon and surrounding landscape.',
      'Balanced editorial typography hierarchy suited for oversized boxy tee silhouettes.',
      'Color-calibrated for direct-to-garment (DTG) and high-density screen print execution.'
    ],
    liveDemoUrl: '#'
  },
  'apparel-zerotwo-jersey': {
    number: '12',
    title: 'ZERO TWO #02 SUBLIMATION JERSEY',
    category: 'Custom Apparel / Sublimation Basketball Jersey',
    client: 'Imprenta Quality Works',
    year: '2024 — 2026',
    role: 'Sportswear & Sublimation Designer',
    deliverables: 'Full Sublimation Print Pattern, Vector Grading, Manga Collage Texture',
    image: 'assets/images/apparel_zerotwo_jersey.jpg',
    description:
      'High-octane full-sublimation basketball jersey design inspired by Zero Two (Darling in the Franxx). Features a deep red monochromatic anime manga background collage, dynamic rib-cage cyber side trims, and high-visibility athletic squad numbering.',
    highlights: [
      'Seamless all-over print (AOP) full sublimation vector layout with collar and armhole trim matching.',
      'Subtle red-and-black anime screentone composite integrated into the athletic mesh fabric.',
      'Production-ready vector separations built to industry sportswear manufacturing standards.'
    ],
    liveDemoUrl: '#'
  },
  'apparel-wasoy-sunflower': {
    number: '13',
    title: 'WASOY: ART SPEAKS OUT YOUNG TEE',
    category: 'Custom Apparel / Typography Streetwear Tee',
    client: 'Wasoy Concept Apparel 2025',
    year: '2024 — 2026',
    role: 'Streetwear Graphic Artist',
    deliverables: 'Custom Vector Typography, Angel Motif, High-Contrast Screen Print Master',
    image: 'assets/images/apparel_wasoy_sunflower.jpg',
    description:
      'A striking pitch-black streetwear oversized t-shirt design highlighting custom ornamental calligraphy and an intricate cherub angel emblem: "WHERE ART SPEAKS Out Young". Presented in a warm golden sunflower horizon campaign mockup.',
    highlights: [
      'Custom hybridized typography blending gothic all-caps letterforms with sweeping cursive script.',
      'Intricate stippled cherub/angel line-art emblem vector designed for sharp screen printing.',
      'Optimized for heavy cotton oversized streetwear fit with high-contrast plastisol ink printing.'
    ],
    liveDemoUrl: '#'
  },
  'social-wendys-tumbler': {
    number: '14',
    title: "WENDY'S: 100TH STORE PROMO",
    category: 'Social Media / Brand Promo Campaign',
    client: "Wendy's Philippines (Friendship Highway)",
    year: '2024 — 2026',
    role: 'Social Media Graphic Artist',
    deliverables: 'Promotional Ad Banner, Social Feed Creatives, Product Mockup Render',
    image: 'assets/images/social_wendys_tumbler.jpg',
    description:
      "High-converting promotional social feed graphic for Wendy's 100th store milestone in Friendship Highway, Pampanga. Highlights the limited edition Wendy's baby-blue insulated tumbler on a clean display plinth with bold typography and corporate brand styling.",
    highlights: [
      'High-impact promotional headline typography designed for scroll-stopping engagement.',
      'Realistic 3D tumbler product rendering with clean pedestal shadow integration.',
      "Strict adherence to Wendy's international brand palette, logos, and promotional layout guidelines."
    ],
    liveDemoUrl: '#'
  },
  'social-imprenta-bigsale': {
    number: '15',
    title: 'IMPRENTA: MID-YEAR BIG SALE',
    category: 'Social Media / Retail Marketing Campaign',
    client: 'Imprenta Novaliches Printing Services',
    year: '2024 — 2026',
    role: 'Lead Marketing & Creative Designer',
    deliverables: 'Multi-Product Collage, Coupon Creative, Social Promo Announcement',
    image: 'assets/images/social_imprenta_bigsale.jpg',
    description:
      'An energetic, high-density marketing creative showcasing custom jerseys, tees, and riding apparel with a prominent 10% OFF discount coupon and textured team backdrop to drive customer inquiries and orders.',
    highlights: [
      'Layered multi-product showcase featuring custom jerseys, polo shirts, and riding long sleeves.',
      'Prominent tear-off ticket coupon motif highlighting special bundle offers and freebies.',
      'Bold typography contrast with textured crowd photo composite driving high campaign conversions.'
    ],
    liveDemoUrl: '#'
  },
  'social-imprenta-samurai': {
    number: '16',
    title: 'IMPRENTA: SAMURAI RIDING SLEEVES',
    category: 'Social Media / Apparel Product Launch',
    client: 'Imprenta Novaliches Printing Services',
    year: '2024 — 2026',
    role: 'Visual Designer & Apparel Stylist',
    deliverables: 'Product Launch Poster, Torn Paper Editorial Layout, Social Showcase',
    image: 'assets/images/social_imprenta_samurai.jpg',
    description:
      'Fierce product launch ad for Imprenta Novaliches featuring the "Dominance Since 2022" Japanese Samurai warrior riding long sleeves. Employs a stylized torn paper transition over an ornate Japanese cloud pattern.',
    highlights: [
      'Dynamic split torn-paper graphic composition separating textured crimson background from product view.',
      '3D realistic floating garment mockup with front-and-back artwork visibility.',
      'Custom typography and Japanese flame & gold cloud ornament integration.'
    ],
    liveDemoUrl: '#'
  },
  'social-wendys-totebag': {
    number: '17',
    title: "WENDY'S: 2ND ANNIVERSARY TOTE BAG",
    category: 'Social Media / Anniversary Event Promo',
    client: "Wendy's Philippines (Tarlac Branch)",
    year: '2024 — 2026',
    role: 'Promotional Graphic Artist',
    deliverables: 'Anniversary Campaign Ad, Merchandise Showcase, Social Poster',
    image: 'assets/images/social_wendys_totebag.jpg',
    description:
      "Celebratory social media campaign poster commemorating the 2nd Anniversary of Wendy's Tarlac branch. Showcases the limited-edition canvas tote bag with custom cute burger, frosty, and Wendy mascots.",
    highlights: [
      'Dynamic radiant sunburst backdrop emphasizing the commemorative occasion.',
      'Crisp product photography placement on a wooden cake plinth for a festive atmosphere.',
      'Vibrant brand typography and clear promotional mechanics tailored for social engagement.'
    ],
    liveDemoUrl: '#'
  }
};

function initProjectsAndModal() {
  // Filter tabs & Project cards
  const filterTabs = document.querySelectorAll('.filter-tab');
  const projectCards = document.querySelectorAll('.project-card');

  // Load More Button
  const loadMoreBtn = document.getElementById('load-more-btn');
  const loadMoreWrap = document.getElementById('load-more-wrap');

  // Dynamically load site settings (Hero text, stats counters, etc.)
  loadDynamicSettings();
  let isAllExpanded = false;
  let currentFilter = 'all';

  function renderProjects() {
    // 1. Gather all project cards matching current category filter
    const matchingCards = [];
    projectCards.forEach((card) => {
      const cat = card.getAttribute('data-category');
      if (currentFilter === 'all' || cat === currentFilter) {
        matchingCards.push(card);
      } else {
        card.style.opacity = '0';
        card.style.display = 'none';
      }
    });

    const totalMatching = matchingCards.length;
    const hasMore = totalMatching > 3;
    const extraCount = totalMatching - 3;

    // 2. Control visibility (top 3 by default or all if expanded)
    matchingCards.forEach((card, index) => {
      if (index < 3 || isAllExpanded) {
        card.style.display = 'flex';
        setTimeout(() => (card.style.opacity = '1'), 30);
      } else {
        card.style.opacity = '0';
        setTimeout(() => (card.style.display = 'none'), 150);
      }
    });

    // 3. Show or hide Load More button based on whether there are > 3 projects in this category
    if (hasMore) {
      if (loadMoreWrap) loadMoreWrap.style.display = 'flex';

      if (loadMoreBtn) {
        const btnText = loadMoreBtn.querySelector('.btn-text');
        if (isAllExpanded) {
          loadMoreBtn.classList.add('expanded');
          if (btnText) btnText.textContent = 'Show Top 3 Only';
        } else {
          loadMoreBtn.classList.remove('expanded');
          if (btnText) btnText.textContent = `See More Works (${extraCount})`;
        }
      }
    } else {
      // 3 or fewer projects: hide the "See More" button
      if (loadMoreWrap) loadMoreWrap.style.display = 'none';
    }
  }

  // Initial Render on page load
  renderProjects();

  function toggleExpand() {
    playSound('click');
    isAllExpanded = !isAllExpanded;
    renderProjects();
  }

  if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', (e) => {
      e.preventDefault();
      toggleExpand();
    });
  }

  filterTabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      playSound('click');
      filterTabs.forEach((t) => t.classList.remove('active'));
      tab.classList.add('active');

      currentFilter = tab.getAttribute('data-filter') || 'all';
      isAllExpanded = false; // Reset to top 3 view on tab change
      renderProjects();
    });
  });

  // Modal logic
  const projectModal = document.getElementById('project-modal');
  const modalClose = projectModal ? projectModal.querySelector('.modal-close-btn') : null;

  projectCards.forEach((card) => {
    card.addEventListener('click', () => {
      const id = card.getAttribute('data-project-id');
      const data = projectData[id];
      if (!data) return;

      playSound('click');
      openProjectModal(data);
    });
  });

  function openProjectModal(data) {
    if (!projectModal) return;

    document.getElementById('modal-project-tag').textContent = `${data.number} // ${data.categoryLabel || data.category}`;
    document.getElementById('modal-project-title').textContent = data.title;
    document.getElementById('modal-project-client').textContent = data.client;
    document.getElementById('modal-project-year').textContent = data.year;
    document.getElementById('modal-project-role').textContent = data.role;
    document.getElementById('modal-project-deliverables').textContent = data.deliverables;
    document.getElementById('modal-project-img').src = data.image;
    document.getElementById('modal-project-desc').textContent = data.description;

    const list = document.getElementById('modal-project-highlights');
    list.innerHTML = '';
    if (Array.isArray(data.highlights)) {
      data.highlights.forEach((hl) => {
        const li = document.createElement('li');
        li.textContent = hl;
        li.style.marginBottom = '8px';
        list.appendChild(li);
      });
    }

    const liveBtn = document.getElementById('modal-live-btn');
    if (liveBtn) {
      liveBtn.onclick = (e) => {
        e.preventDefault();
        closeProjectModal();
        const contactModal = document.getElementById('contact-modal');
        if (contactModal) {
          const msgInput = document.getElementById('contact-message');
          if (msgInput) {
            msgInput.value = `Hi Brian Joshua, I saw your artwork "${data.title}" and would like to commission a similar project...`;
          }
          contactModal.classList.add('active');
          document.body.style.overflow = 'hidden';
        }
      };
    }

    projectModal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  if (modalClose) {
    modalClose.addEventListener('click', closeProjectModal);
  }

  if (projectModal) {
    projectModal.addEventListener('click', (e) => {
      if (e.target === projectModal) closeProjectModal();
    });
  }

  function closeProjectModal() {
    if (!projectModal) return;
    playSound('click');
    projectModal.classList.remove('active');
    document.body.style.overflow = '';
  }

  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeProjectModal();
      closeContactModal();
    }
  });

  // Also dynamically load site settings (Hero text, stats counters, etc.)
  loadDynamicSettings();
}

async function loadDynamicSettings() {
  try {
    const res = await fetch('api/settings.php');
    if (!res.ok) return;
    const data = await res.json();
    if (data.success && data.settings) {
      const s = data.settings;
      if (s.hero_name) {
        const heroNameEl = document.querySelector('.hero-name');
        if (heroNameEl) heroNameEl.innerHTML = s.hero_name.replace(' ', '<br>');
      }
      if (s.hero_bio) {
        const bioEl = document.querySelector('.hero-bio');
        if (bioEl) bioEl.textContent = s.hero_bio;
      }
      if (s.hero_location) {
        const locEl = document.querySelector('.hero-worldwide-badge span');
        if (locEl) locEl.textContent = s.hero_location;
      }
      if (s.availability_status) {
        const statusEl = document.querySelector('.availability-tag span:nth-child(2)');
        if (statusEl) statusEl.textContent = s.availability_status;
      }
    }
  } catch (e) {
    // Settings offline fallback is ok
  }
}

/* ==========================================================================
   6. CONTACT SYSTEM & CLIPBOARD
   ========================================================================== */
function initContactSystem() {
  // Copy to clipboard cards (for elements with data-copy)
  const copyCards = document.querySelectorAll('.contact-method-card[data-copy]');
  copyCards.forEach((card) => {
    card.addEventListener('click', (e) => {
      const copyVal = card.getAttribute('data-copy');
      const label = card.getAttribute('data-label') || 'Information';
      if (copyVal) {
        navigator.clipboard
          .writeText(copyVal)
          .then(() => {
            playSound('chime');
            showToast(`✓ Copied ${label} (${copyVal}) to clipboard!`);
          })
          .catch(() => {
            showToast(`Selected: ${copyVal}`);
          });
      }
    });
  });

  // Contact Modal
  const contactModal = document.getElementById('contact-modal');
  const hireBtns = document.querySelectorAll('.trigger-contact-modal');
  const contactClose = contactModal ? contactModal.querySelector('.modal-close-btn') : null;
  const contactForm = document.getElementById('quick-contact-form');

  hireBtns.forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      playSound('click');
      if (contactModal) {
        contactModal.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    });
  });

  if (contactClose) {
    contactClose.addEventListener('click', closeContactModal);
  }

  if (contactModal) {
    contactModal.addEventListener('click', (e) => {
      if (e.target === contactModal) closeContactModal();
    });
  }

  if (contactForm) {
    contactForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      const submitBtn = document.getElementById('contact-submit-btn') || contactForm.querySelector('button[type="submit"]');
      const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '<span>Submit Commission Request</span><span style="font-size: 1.1rem;">✦</span>';

      // Set loading state
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.75';
        submitBtn.style.cursor = 'wait';
        submitBtn.innerHTML = `
          <span class="btn-spinner"></span>
          <span>Sending to Brian's Inbox...</span>
        `;
      }

      // Collect form data
      const formData = new FormData(contactForm);
      const payload = {};
      formData.forEach((value, key) => {
        payload[key] = value;
      });

      // Save inquiry into Admin database
      fetch('api/messages.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      }).catch((e) => console.warn('Database logging note:', e));

      try {
        const response = await fetch('https://formsubmit.co/ajax/Briantanael187@gmail.com', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (response.ok && (result.success === 'true' || result.success === true)) {
          playSound('chime');
          showToast('✓ Project inquiry sent directly to Brian Joshua! Check your email for confirmation.');
          contactForm.reset();
          setTimeout(closeContactModal, 1800);
        } else if (result.message && result.message.toLowerCase().includes('activation')) {
          // If activation is pending, ensure inquiry is immediately dispatched via email client
          playSound('chime');
          showToast('✦ Form initialized! Opening your email app to deliver inquiry directly...');
          const subject = encodeURIComponent(`Commission Inquiry: ${payload.artwork_service || 'New Project'} — ${payload.name || ''}`);
          const body = encodeURIComponent(
            `Hi Brian Joshua,\n\n` +
            `I would like to commission an artwork / project.\n\n` +
            `• Name: ${payload.name || ''}\n` +
            `• Email: ${payload.email || ''}\n` +
            `• Artwork Service: ${payload.artwork_service || ''}\n` +
            `• Project Scope: ${payload.project_budget || ''}\n\n` +
            `Creative Brief & Vision:\n${payload.message || ''}\n`
          );
          window.location.href = `mailto:Briantanael187@gmail.com?subject=${subject}&body=${body}`;
          contactForm.reset();
          setTimeout(closeContactModal, 2500);
        } else {
          throw new Error(result.message || 'Submission failed');
        }
      } catch (err) {
        console.warn('FormSubmit AJAX issue, falling back:', err);
        playSound('click');
        showToast('✦ Notice: Opening your default email app to send directly...');
        const subject = encodeURIComponent(`Commission Inquiry: ${payload.artwork_service || 'New Project'} — ${payload.name || ''}`);
        const body = encodeURIComponent(
          `Hi Brian Joshua,\n\n` +
          `I would like to commission an artwork / project.\n\n` +
          `• Name: ${payload.name || ''}\n` +
          `• Email: ${payload.email || ''}\n` +
          `• Artwork Service: ${payload.artwork_service || ''}\n` +
          `• Project Scope: ${payload.project_budget || ''}\n\n` +
          `Creative Brief & Vision:\n${payload.message || ''}\n`
        );
        window.location.href = `mailto:Briantanael187@gmail.com?subject=${subject}&body=${body}`;
        setTimeout(closeContactModal, 1500);
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.style.opacity = '';
          submitBtn.style.cursor = '';
          submitBtn.innerHTML = originalBtnHtml;
        }
      }
    });

    // Dynamic direct email link updater
    const directEmailLink = document.getElementById('direct-email-link');
    if (directEmailLink) {
      const updateDirectMail = () => {
        const name = document.getElementById('contact-name')?.value || '';
        const service = document.getElementById('contact-service')?.value || 'Creative Project';
        const budget = document.getElementById('contact-budget')?.value || '';
        const message = document.getElementById('contact-message')?.value || '';
        const subj = encodeURIComponent(`Commission Inquiry: ${service} — ${name}`);
        const bdy = encodeURIComponent(`Name: ${name}\nService: ${service}\nScope: ${budget}\n\nCreative Brief:\n${message}`);
        directEmailLink.href = `mailto:Briantanael187@gmail.com?subject=${subj}&body=${bdy}`;
      };
      contactForm.addEventListener('input', updateDirectMail);
      contactForm.addEventListener('change', updateDirectMail);
    }
  }
}

function closeContactModal() {
  const contactModal = document.getElementById('contact-modal');
  if (contactModal && contactModal.classList.contains('active')) {
    playSound('click');
    contactModal.classList.remove('active');
    document.body.style.overflow = '';
  }
}

function showToast(message) {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.innerHTML = `<span style="color: #ff1e2d; font-size: 1.1rem;">✦</span> <span>${message}</span>`;
  container.appendChild(toast);

  setTimeout(() => toast.classList.add('show'), 10);

  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 400);
  }, 3500);
}

/* ==========================================================================
   7. WEB AUDIO API SYNTHESIZED SOUND ENGINE
   ========================================================================== */
let audioCtx = null;
let soundEnabled = true;

function initSoundEngine() {
  const toggleBtn = document.getElementById('sound-toggle');
  if (!toggleBtn) return;

  const savedSound = localStorage.getItem('rayhan_sound_fx');
  if (savedSound === 'disabled') {
    soundEnabled = false;
    updateSoundBtnState(toggleBtn);
  }

  toggleBtn.addEventListener('click', () => {
    soundEnabled = !soundEnabled;
    localStorage.setItem('rayhan_sound_fx', soundEnabled ? 'enabled' : 'disabled');
    updateSoundBtnState(toggleBtn);
    if (soundEnabled) {
      playSound('chime');
      showToast('Sound Effects Enabled 🔊');
    } else {
      showToast('Sound Effects Muted 🔇');
    }
  });

  // Attach hover sounds to interactive elements
  const hoverables = document.querySelectorAll('button, a, .project-card, .skill-pill, .filter-tab');
  hoverables.forEach((el) => {
    el.addEventListener('mouseenter', () => playSound('hover'));
  });
}

function updateSoundBtnState(btn) {
  if (soundEnabled) {
    btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>`;
    btn.setAttribute('title', 'Sound Effects: ON (Click to mute)');
  } else {
    btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><line x1="23" y1="9" x2="17" y2="15"></line><line x1="17" y1="9" x2="23" y2="15"></line></svg>`;
    btn.setAttribute('title', 'Sound Effects: MUTED (Click to enable)');
  }
}

function getAudioContext() {
  if (!audioCtx) {
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (AudioContext) {
      audioCtx = new AudioContext();
    }
  }
  if (audioCtx && audioCtx.state === 'suspended') {
    audioCtx.resume();
  }
  return audioCtx;
}

function playSound(type) {
  if (!soundEnabled) return;
  const ctx = getAudioContext();
  if (!ctx) return;

  try {
    const now = ctx.currentTime;
    if (type === 'hover') {
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(480, now);
      osc.frequency.exponentialRampToValueAtTime(760, now + 0.04);
      gain.gain.setValueAtTime(0.015, now);
      gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.04);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start(now);
      osc.stop(now + 0.04);
    } else if (type === 'click') {
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'triangle';
      osc.frequency.setValueAtTime(220, now);
      osc.frequency.exponentialRampToValueAtTime(80, now + 0.06);
      gain.gain.setValueAtTime(0.04, now);
      gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.06);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start(now);
      osc.stop(now + 0.06);
    } else if (type === 'chime') {
      [587.33, 880].forEach((freq, i) => {
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(freq, now + i * 0.06);
        gain.gain.setValueAtTime(0.035, now + i * 0.06);
        gain.gain.exponentialRampToValueAtTime(0.0001, now + i * 0.06 + 0.35);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(now + i * 0.06);
        osc.stop(now + i * 0.06 + 0.35);
      });
    }
  } catch (err) {
    // Graceful fallback if audio is blocked by user agent
  }
}

/* ==========================================================================
   8. BACK TO TOP
   ========================================================================== */
function initBackToTop() {
  const btn = document.getElementById('back-to-top-btn');
  if (btn) {
    btn.addEventListener('click', () => {
      playSound('click');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }
}
