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
    description:
      'A collection of high-converting, attention-grabbing social media graphics crafted for viral reach and modern brand aesthetics. Built with high-contrast typography, color grading, and dynamic visual layouts across Instagram, Facebook, and promotional campaigns.',
    highlights: [
      'Engineered bold visual hierarchy tailored for scroll-stopping engagement on modern feeds.',
      'Utilized Photoshop, CorelDRAW, and Canva for versatile, fast-turnaround campaign packages.',
      'Created unified visual identity systems for multi-platform marketing launches.'
    ],
    liveDemoUrl: '#'
  },
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
    description:
      'Custom graphic designs engineered specifically for apparel production, streetwear brands, and event merchandise. Focused on intricate line art, typography composition, and color-separated vector files ready for screen printing and direct-to-garment (DTG) execution.',
    highlights: [
      'Mastery in vector separation, print specifications, and garment mockups.',
      'Delivered over 100+ unique shirt and hoodie designs for streetwear brands and client collections.',
      'Blends hand-drawn traditional character art with futuristic cyber-grunge typography.'
    ],
    liveDemoUrl: '#'
  },
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
    description:
      'Expansive digital art creations combining painterly brushwork with contemporary sci-fi and illustrative character art. Each piece balances dramatic rim lighting, anatomy precision, and atmospheric world-building.',
    highlights: [
      'Created utilizing Adobe Photoshop, graphic tablets, and AI-assisted workflows.',
      'Rich cinematic color palettes with high-depth contrast and rendering.',
      'Versatile styles ranging from anime/manga to hyper-stylized digital realism.'
    ],
    liveDemoUrl: '#'
  },
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
    description:
      'Foundational traditional artworks rooted in classic drawing techniques, portraiture, ink hatching, and vibrant painting mediums. Demonstrates deep understanding of anatomy, lighting, values, and organic textures that elevate digital work.',
    highlights: [
      'Specialized in charcoal, graphite realism, acrylic, and watercolor media.',
      'Over 5+ years of dedicated traditional sketchbooks and portfolio studies.',
      'Forms the irreplaceable organic foundation behind all digital and apparel works.'
    ],
    liveDemoUrl: '#'
  }
};

async function initProjectsAndModal() {
  const grid = document.getElementById('projects-grid');

  // Attempt to fetch live dynamic projects from backend API
  try {
    const res = await fetch('api/projects.php');
    if (res.ok) {
      const data = await res.json();
      if (data.success && Array.isArray(data.projects) && data.projects.length > 0) {
        projectData = {};
        data.projects.forEach((p, idx) => {
          const numStr = String(idx + 1).padStart(2, '0');
          const idKey = String(p.id);
          projectData[idKey] = {
            id: p.id,
            number: numStr,
            title: p.title,
            category: p.category,
            categoryLabel: p.category_label || p.category,
            client: p.client || 'Creative Client',
            year: p.year || '2024 — 2026',
            role: p.role || 'Artist',
            deliverables: p.deliverables || 'Visual Artworks',
            image: p.image,
            description: p.description || '',
            highlights: Array.isArray(p.highlights) ? p.highlights : [],
            liveDemoUrl: p.live_demo_url || '#'
          };
        });
      }
    }
  } catch (err) {
    // Graceful offline fallback
    console.info('Using local projects fallback data.');
  }

  // Render project cards into #projects-grid
  if (grid) {
    const projectKeys = Object.keys(projectData);
    grid.innerHTML = projectKeys.map((key, idx) => {
      const item = projectData[key];
      const isExtra = idx >= 3 ? ' extra-project' : '';
      const displayStyle = idx >= 3 ? 'display: none;' : '';
      return `
        <div class="project-card${isExtra}" data-category="${item.category}" data-project-id="${key}" style="${displayStyle}">
          <div class="project-thumbnail-wrapper">
            <img src="${item.image}" alt="${item.title}" class="project-thumbnail" onerror="this.src='assets/images/project_veloce.jpg'" />
            <span class="project-badge-tag">${item.number} // ${item.category.toUpperCase()}</span>
          </div>
          <div class="project-card-footer">
            <div class="project-info">
              <div class="project-number">${item.number}</div>
              <div class="project-meta">
                <h3 class="project-title">${item.title}</h3>
                <span class="project-category">${item.categoryLabel}</span>
              </div>
            </div>
            <div class="project-arrow">→</div>
          </div>
        </div>
      `;
    }).join('');

    // Re-attach hover sounds
    if (typeof playSound === 'function') {
      grid.querySelectorAll('.project-card').forEach((c) => {
        c.addEventListener('mouseenter', () => playSound('hover'));
      });
    }
  }

  // Filter tabs logic
  const filterTabs = document.querySelectorAll('.filter-tab');
  const projectCards = document.querySelectorAll('.project-card');
  const toggleAllBtn = document.getElementById('toggle-all-projects');
  const viewAllText = document.getElementById('view-all-text');
  let isAllExpanded = false;

  if (toggleAllBtn) {
    toggleAllBtn.addEventListener('click', (e) => {
      e.preventDefault();
      playSound('click');
      isAllExpanded = !isAllExpanded;
      const extraProjects = document.querySelectorAll('.extra-project');

      extraProjects.forEach((card) => {
        if (isAllExpanded) {
          card.style.display = 'flex';
          setTimeout(() => (card.style.opacity = '1'), 50);
        } else {
          card.style.opacity = '0';
          setTimeout(() => (card.style.display = 'none'), 200);
        }
      });

      if (viewAllText) {
        viewAllText.textContent = isAllExpanded ? 'Show Top 3 Only' : 'View All Projects';
      }
    });
  }

  filterTabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      playSound('click');
      filterTabs.forEach((t) => t.classList.remove('active'));
      tab.classList.add('active');

      const filter = tab.getAttribute('data-filter');
      projectCards.forEach((card) => {
        const cat = card.getAttribute('data-category');
        const isExtra = card.classList.contains('extra-project');

        if (filter === 'all') {
          if (!isExtra || isAllExpanded) {
            card.style.display = 'flex';
            setTimeout(() => (card.style.opacity = '1'), 50);
          } else {
            card.style.display = 'none';
          }
        } else if (cat === filter) {
          card.style.display = 'flex';
          setTimeout(() => (card.style.opacity = '1'), 50);
        } else {
          card.style.opacity = '0';
          setTimeout(() => (card.style.display = 'none'), 200);
        }
      });
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
  // Copy to clipboard cards
  const copyCards = document.querySelectorAll('.contact-method-card');
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
