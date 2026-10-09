/**
 * Brian Joshua Portfolio — Admin Dashboard Logic
 */

document.addEventListener('DOMContentLoaded', () => {
  initNavigation();
  initProjects();
  initMessages();
  initSettings();
  initSkills();
  initAccount();
  initImageUpload();
  initLogout();
  initMobileMenu();

  // Load initial data
  loadProjects();
  loadMessages();
  loadSettings();
  loadSkills();
});

/* ==========================================================================
   NAVIGATION & TABS
   ========================================================================== */
function initNavigation() {
  const navLinks = document.querySelectorAll('.nav-link-item');
  navLinks.forEach((link) => {
    link.addEventListener('click', () => {
      const tab = link.getAttribute('data-tab');
      switchTab(tab);
    });
  });

  const quickAdd = document.getElementById('btn-quick-add-project');
  if (quickAdd) {
    quickAdd.addEventListener('click', () => {
      openProjectDialog();
    });
  }
}

function switchTab(tabId) {
  // Update sidebar active class
  document.querySelectorAll('.nav-link-item').forEach((l) => {
    l.classList.toggle('active', l.getAttribute('data-tab') === tabId);
  });

  // Update views
  document.querySelectorAll('.admin-tab-view').forEach((v) => {
    v.classList.remove('active');
  });

  const targetView = document.getElementById(`tab-${tabId}`);
  if (targetView) {
    targetView.classList.add('active');
  }

  // Update breadcrumb
  const breadcrumb = document.getElementById('breadcrumb-page-name');
  if (breadcrumb) {
    const titles = {
      overview: 'OVERVIEW',
      projects: 'SELECTED WORKS',
      messages: 'CLIENT INQUIRIES',
      settings: 'HERO & PROFILE SETTINGS',
      skills: 'SKILLS & TOOLSET',
      hostforge: 'HOSTFORGE DEPLOYMENT',
      account: 'ACCOUNT & SECURITY'
    };
    breadcrumb.textContent = titles[tabId] || tabId.toUpperCase();
  }

  // Close mobile sidebar if open
  const sidebar = document.getElementById('admin-sidebar');
  if (sidebar && sidebar.classList.contains('open')) {
    sidebar.classList.remove('open');
  }
}

/* ==========================================================================
   PROJECTS MANAGEMENT
   ========================================================================== */
let allProjects = [];
let currentCategoryFilter = 'all';

function initProjects() {
  const addBtn = document.getElementById('btn-add-project');
  if (addBtn) {
    addBtn.addEventListener('click', () => openProjectDialog());
  }

  const saveBtn = document.getElementById('save-project-btn');
  if (saveBtn) {
    saveBtn.addEventListener('click', saveProject);
  }

  // Filter tabs
  const filterTabs = document.querySelectorAll('[data-admin-filter]');
  filterTabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      filterTabs.forEach((t) => t.classList.remove('active'));
      tab.classList.add('active');
      currentCategoryFilter = tab.getAttribute('data-admin-filter');
      renderProjectsTable();
    });
  });
}

async function loadProjects() {
  try {
    const res = await fetch('../api/projects.php');
    const data = await res.json();
    if (data.success) {
      allProjects = data.projects || [];
      document.getElementById('badge-projects-count').textContent = allProjects.length;
      document.getElementById('metric-projects-count').textContent = allProjects.length;
      renderProjectsTable();
    }
  } catch (err) {
    console.error('Failed to load projects:', err);
    showToast('Failed to connect to Projects API');
  }
}

function renderProjectsTable() {
  const tbody = document.querySelector('#projects-table tbody');
  if (!tbody) return;

  const filtered = currentCategoryFilter === 'all'
    ? allProjects
    : allProjects.filter((p) => p.category === currentCategoryFilter);

  if (filtered.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 32px; color: var(--admin-text-muted);">No projects found in this category. Click "+ Add New Artwork" above.</td></tr>`;
    return;
  }

  tbody.innerHTML = filtered.map((p) => {
    const thumbUrl = p.image.startsWith('http') || p.image.startsWith('/') ? p.image : `../${p.image}`;
    return `
      <tr>
        <td>
          <img src="${thumbUrl}" class="table-thumb" alt="${escapeHtml(p.title)}" onerror="this.src='../assets/images/project_veloce.jpg'">
        </td>
        <td style="font-weight: 700; color: #fff;">
          ${escapeHtml(p.title)}
          <div style="font-size: 0.72rem; color: var(--admin-text-muted); font-weight: normal; font-family: var(--font-mono);">${escapeHtml(p.role || 'Artist')}</div>
        </td>
        <td>
          <span class="badge-tag badge-${p.category}">${p.category}</span>
        </td>
        <td>${escapeHtml(p.client || '—')}</td>
        <td>${escapeHtml(p.year || '—')}</td>
        <td><span style="font-family: var(--font-mono); font-size: 0.8rem;">#${p.sort_order}</span></td>
        <td>
          ${p.is_featured == 1 
            ? '<span style="color: var(--admin-success); font-weight: 600;">✓ Visible</span>' 
            : '<span style="color: var(--admin-text-muted);">Hidden</span>'}
        </td>
        <td>
          <div class="action-btn-group">
            <button class="btn-icon" onclick="editProject(${p.id})">Edit</button>
            <button class="btn-icon btn-danger" onclick="deleteProject(${p.id})">Delete</button>
          </div>
        </td>
      </tr>
    `;
  }).join('');
}

function openProjectDialog(project = null) {
  const modal = document.getElementById('project-modal-dialog');
  const heading = document.getElementById('modal-project-heading');
  const form = document.getElementById('project-edit-form');
  const preview = document.getElementById('image-preview');
  const uploadPrompt = document.getElementById('image-upload-prompt');

  form.reset();

  if (project) {
    heading.textContent = 'Edit Artwork';
    document.getElementById('proj-id').value = project.id;
    document.getElementById('proj-action').value = 'update';
    document.getElementById('proj-title').value = project.title || '';
    document.getElementById('proj-category').value = project.category || 'social';
    document.getElementById('proj-cat-label').value = project.category_label || '';
    document.getElementById('proj-client').value = project.client || '';
    document.getElementById('proj-year').value = project.year || '';
    document.getElementById('proj-role').value = project.role || '';
    document.getElementById('proj-deliverables').value = project.deliverables || '';
    document.getElementById('proj-order').value = project.sort_order || 0;
    document.getElementById('proj-image-url').value = project.image || '';
    document.getElementById('proj-desc').value = project.description || '';
    document.getElementById('proj-highlights').value = Array.isArray(project.highlights) ? project.highlights.join('\n') : '';
    document.getElementById('proj-link').value = project.live_demo_url || '#';
    document.getElementById('proj-featured').value = project.is_featured;

    if (project.image) {
      preview.src = project.image.startsWith('http') || project.image.startsWith('/') ? project.image : `../${project.image}`;
      preview.style.display = 'block';
      uploadPrompt.style.display = 'none';
    } else {
      preview.style.display = 'none';
      uploadPrompt.style.display = 'block';
    }
  } else {
    heading.textContent = 'Add New Artwork';
    document.getElementById('proj-id').value = '';
    document.getElementById('proj-action').value = 'create';
    document.getElementById('proj-image-url').value = 'assets/images/project_veloce.jpg';
    document.getElementById('proj-order').value = allProjects.length + 1;
    preview.style.display = 'none';
    uploadPrompt.style.display = 'block';
  }

  modal.classList.add('active');
}

function closeProjectDialog() {
  const modal = document.getElementById('project-modal-dialog');
  modal.classList.remove('active');
}

function editProject(id) {
  const project = allProjects.find((p) => p.id === id);
  if (project) {
    openProjectDialog(project);
  }
}

async function saveProject() {
  const form = document.getElementById('project-edit-form');
  const action = document.getElementById('proj-action').value;
  const id = document.getElementById('proj-id').value;
  const title = document.getElementById('proj-title').value.trim();
  const category = document.getElementById('proj-category').value;
  const category_label = document.getElementById('proj-cat-label').value.trim();
  const client = document.getElementById('proj-client').value.trim();
  const year = document.getElementById('proj-year').value.trim();
  const role = document.getElementById('proj-role').value.trim();
  const deliverables = document.getElementById('proj-deliverables').value.trim();
  const sort_order = document.getElementById('proj-order').value;
  const image = document.getElementById('proj-image-url').value.trim() || 'assets/images/project_veloce.jpg';
  const description = document.getElementById('proj-desc').value.trim();
  const highlights = document.getElementById('proj-highlights').value;
  const live_demo_url = document.getElementById('proj-link').value.trim();
  const is_featured = document.getElementById('proj-featured').value;

  if (!title) {
    alert('Please enter a project title');
    return;
  }

  const payload = {
    action,
    id,
    title,
    category,
    category_label,
    client,
    year,
    role,
    deliverables,
    sort_order,
    image,
    description,
    highlights,
    live_demo_url,
    is_featured
  };

  const saveBtn = document.getElementById('save-project-btn');
  saveBtn.disabled = true;
  saveBtn.innerHTML = '<span>Saving...</span>';

  try {
    const res = await fetch('../api/projects.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();

    if (res.ok && data.success) {
      showToast('✓ Artwork saved successfully!');
      closeProjectDialog();
      await loadProjects();
    } else {
      alert(data.error || 'Failed to save artwork');
    }
  } catch (err) {
    console.error(err);
    alert('Network error while saving artwork');
  } finally {
    saveBtn.disabled = false;
    saveBtn.innerHTML = '<span>Save Artwork</span><span style="font-size: 1.1rem;">✓</span>';
  }
}

async function deleteProject(id) {
  const project = allProjects.find((p) => p.id === id);
  const title = project ? project.title : 'this project';

  if (!confirm(`Are you sure you want to delete "${title}"? This cannot be undone.`)) {
    return;
  }

  try {
    const res = await fetch('../api/projects.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete', id })
    });
    const data = await res.json();
    if (data.success) {
      showToast('✓ Project deleted');
      await loadProjects();
    } else {
      alert(data.error || 'Failed to delete project');
    }
  } catch (err) {
    console.error(err);
    alert('Network error during deletion');
  }
}

/* ==========================================================================
   IMAGE UPLOAD LOGIC
   ========================================================================== */
function initImageUpload() {
  const dropArea = document.getElementById('image-drop-area');
  const fileInput = document.getElementById('proj-image-file');
  const preview = document.getElementById('image-preview');
  const uploadPrompt = document.getElementById('image-upload-prompt');
  const imageUrlInput = document.getElementById('proj-image-url');

  if (!dropArea || !fileInput) return;

  dropArea.addEventListener('click', () => fileInput.click());

  dropArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropArea.style.borderColor = 'var(--admin-accent)';
  });

  dropArea.addEventListener('dragleave', () => {
    dropArea.style.borderColor = '';
  });

  dropArea.addEventListener('drop', (e) => {
    e.preventDefault();
    dropArea.style.borderColor = '';
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      handleFileUpload(e.dataTransfer.files[0]);
    }
  });

  fileInput.addEventListener('change', () => {
    if (fileInput.files && fileInput.files[0]) {
      handleFileUpload(fileInput.files[0]);
    }
  });

  async function handleFileUpload(file) {
    const formData = new FormData();
    formData.append('image', file);

    uploadPrompt.innerHTML = '<div style="color: var(--admin-accent);">Uploading image to server...</div>';

    try {
      const res = await fetch('../api/upload.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();

      if (res.ok && data.success) {
        imageUrlInput.value = data.url;
        preview.src = `../${data.url}`;
        preview.style.display = 'block';
        uploadPrompt.style.display = 'none';
        showToast('✓ Image uploaded successfully!');
      } else {
        alert(data.error || 'Upload failed');
        uploadPrompt.innerHTML = '<div style="color: #ff5252;">Upload failed. Click to retry.</div>';
      }
    } catch (err) {
      console.error(err);
      alert('Network error during file upload');
      uploadPrompt.innerHTML = '<div style="color: #ff5252;">Error. Click to retry.</div>';
    }
  }
}

/* ==========================================================================
   MESSAGES & INQUIRIES MANAGEMENT
   ========================================================================== */
let allMessages = [];
let currentMsgFilter = 'all';

function initMessages() {
  const filterTabs = document.querySelectorAll('[data-msg-filter]');
  filterTabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      filterTabs.forEach((t) => t.classList.remove('active'));
      tab.classList.add('active');
      currentMsgFilter = tab.getAttribute('data-msg-filter');
      renderMessagesTable();
    });
  });
}

async function loadMessages() {
  try {
    const res = await fetch('../api/messages.php');
    const data = await res.json();
    if (data.success) {
      allMessages = data.messages || [];
      const unreadCount = data.unread_count || 0;
      document.getElementById('badge-messages-unread').textContent = unreadCount;
      document.getElementById('metric-unread-count').textContent = unreadCount;

      renderMessagesTable();
      renderOverviewMessagesTable();
    }
  } catch (err) {
    console.error('Failed to load messages:', err);
  }
}

function renderOverviewMessagesTable() {
  const tbody = document.querySelector('#overview-inquiries-table tbody');
  if (!tbody) return;

  if (allMessages.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 20px; color: var(--admin-text-muted);">No inquiries yet. Submissions from the contact modal will appear here.</td></tr>`;
    return;
  }

  const recent = allMessages.slice(0, 5);
  tbody.innerHTML = recent.map((m) => `
    <tr>
      <td style="font-weight: 600; color: #fff;">${escapeHtml(m.name)}</td>
      <td>${escapeHtml(m.artwork_service || 'Commission')}</td>
      <td>${escapeHtml(m.project_budget || '—')}</td>
      <td><span class="badge-tag badge-${m.status}">${m.status}</span></td>
      <td><button class="btn-icon" onclick="viewInquiry(${m.id})">Open</button></td>
    </tr>
  `).join('');
}

function renderMessagesTable() {
  const tbody = document.querySelector('#messages-table tbody');
  if (!tbody) return;

  const filtered = currentMsgFilter === 'all'
    ? allMessages
    : allMessages.filter((m) => m.status === currentMsgFilter);

  if (filtered.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 28px; color: var(--admin-text-muted);">No inquiries found with status "${currentMsgFilter}".</td></tr>`;
    return;
  }

  tbody.innerHTML = filtered.map((m) => {
    const dateFormatted = m.created_at ? new Date(m.created_at).toLocaleDateString() : '—';
    return `
      <tr>
        <td style="font-family: var(--font-mono); font-size: 0.78rem;">${dateFormatted}</td>
        <td style="font-weight: 700; color: #fff;">${escapeHtml(m.name)}</td>
        <td><a href="mailto:${escapeHtml(m.email)}" style="color: var(--admin-accent); text-decoration: none;">${escapeHtml(m.email)}</a></td>
        <td>${escapeHtml(m.artwork_service || '—')}</td>
        <td>${escapeHtml(m.project_budget || '—')}</td>
        <td><span class="badge-tag badge-${m.status}">${m.status}</span></td>
        <td>
          <div class="action-btn-group">
            <button class="btn-icon" onclick="viewInquiry(${m.id})">View Brief</button>
            <button class="btn-icon btn-danger" onclick="deleteInquiry(${m.id})">Delete</button>
          </div>
        </td>
      </tr>
    `;
  }).join('');
}

function viewInquiry(id) {
  const msg = allMessages.find((m) => m.id === id);
  if (!msg) return;

  const modal = document.getElementById('inquiry-modal-dialog');
  const body = document.getElementById('inquiry-modal-body');
  const footer = document.getElementById('inquiry-modal-footer');

  // Mark as read automatically if unread
  if (msg.status === 'unread') {
    updateMessageStatus(msg.id, 'read', false);
  }

  body.innerHTML = `
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px; background: rgba(0,0,0,0.25); padding: 16px; border-radius: 6px; border: 1px solid var(--admin-border);">
      <div>
        <div style="font-size: 0.75rem; color: var(--admin-text-muted); font-family: var(--font-mono);">CLIENT NAME</div>
        <div style="font-size: 1.05rem; font-weight: 700; color: #fff;">${escapeHtml(msg.name)}</div>
      </div>
      <div>
        <div style="font-size: 0.75rem; color: var(--admin-text-muted); font-family: var(--font-mono);">EMAIL ADDRESS</div>
        <div style="font-size: 0.95rem; color: var(--admin-accent); font-weight: 600;">${escapeHtml(msg.email)}</div>
      </div>
      <div>
        <div style="font-size: 0.75rem; color: var(--admin-text-muted); font-family: var(--font-mono);">ARTWORK SERVICE</div>
        <div style="color: #fff;">${escapeHtml(msg.artwork_service || 'General Project')}</div>
      </div>
      <div>
        <div style="font-size: 0.75rem; color: var(--admin-text-muted); font-family: var(--font-mono);">BUDGET SCOPE</div>
        <div style="color: #fff;">${escapeHtml(msg.project_budget || 'Standard')}</div>
      </div>
    </div>

    <div style="margin-bottom: 14px;">
      <div style="font-size: 0.8rem; font-family: var(--font-mono); color: var(--admin-accent); margin-bottom: 6px; text-transform: uppercase;">
        Creative Brief & Client Vision:
      </div>
      <div style="background: rgba(0,0,0,0.4); padding: 18px; border-radius: 6px; border: 1px solid var(--admin-border); color: #fff; line-height: 1.6; white-space: pre-wrap; font-size: 0.92rem;">
        ${escapeHtml(msg.message)}
      </div>
    </div>
  `;

  const replySubject = encodeURIComponent(`Re: Commission Inquiry: ${msg.artwork_service || 'Portfolio Project'} — Brian Joshua Tanael`);
  const replyBody = encodeURIComponent(`Hi ${msg.name},\n\nThank you for reaching out regarding your project for ${msg.artwork_service || 'artwork'}!\n\nBest regards,\nBrian Joshua Tanael`);

  footer.innerHTML = `
    <a href="mailto:${msg.email}?subject=${replySubject}&body=${replyBody}" class="btn-primary" target="_blank">
      <span>✉ Reply via Email</span>
    </a>
    <button class="btn-icon" onclick="updateMessageStatus(${msg.id}, 'archived')">Archive</button>
    <button class="btn-icon btn-danger" onclick="deleteInquiry(${msg.id})">Delete</button>
    <button class="btn-icon" onclick="closeInquiryDialog()">Close</button>
  `;

  modal.classList.add('active');
}

function closeInquiryDialog() {
  document.getElementById('inquiry-modal-dialog').classList.remove('active');
}

async function updateMessageStatus(id, newStatus, reload = true) {
  try {
    const res = await fetch('../api/messages.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'update_status', id, status: newStatus })
    });
    if (reload) {
      showToast(`✓ Marked as ${newStatus}`);
      closeInquiryDialog();
      await loadMessages();
    } else {
      // update local
      const msg = allMessages.find((m) => m.id === id);
      if (msg) msg.status = newStatus;
      const unreadCount = allMessages.filter((m) => m.status === 'unread').length;
      document.getElementById('badge-messages-unread').textContent = unreadCount;
      document.getElementById('metric-unread-count').textContent = unreadCount;
    }
  } catch (err) {
    console.error(err);
  }
}

async function deleteInquiry(id) {
  if (!confirm('Are you sure you want to delete this message?')) return;

  try {
    const res = await fetch('../api/messages.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete', id })
    });
    const data = await res.json();
    if (data.success) {
      showToast('✓ Message deleted');
      closeInquiryDialog();
      await loadMessages();
    }
  } catch (err) {
    console.error(err);
    alert('Failed to delete message');
  }
}

/* ==========================================================================
   SETTINGS & PROFILE
   ========================================================================== */
function initSettings() {
  const form = document.getElementById('settings-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('save-settings-btn');
    btn.disabled = true;
    btn.innerHTML = '<span>Saving...</span>';

    const formData = new FormData(form);
    const settings = {};
    formData.forEach((val, key) => {
      settings[key] = val;
    });

    try {
      const res = await fetch('../api/settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ settings })
      });
      const data = await res.json();

      if (data.success) {
        showToast('✓ Settings updated successfully!');
      } else {
        alert(data.error || 'Failed to update settings');
      }
    } catch (err) {
      console.error(err);
      alert('Network error saving settings');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<span>Save All Profile Settings</span><span style="font-size: 1.1rem;">✓</span>';
    }
  });
}

async function loadSettings() {
  try {
    const res = await fetch('../api/settings.php');
    const data = await res.json();
    if (data.success && data.settings) {
      const s = data.settings;
      if (document.getElementById('set-hero-name')) document.getElementById('set-hero-name').value = s.hero_name || '';
      if (document.getElementById('set-hero-role')) document.getElementById('set-hero-role').value = s.hero_role || '';
      if (document.getElementById('set-hero-bio')) document.getElementById('set-hero-bio').value = s.hero_bio || '';
      if (document.getElementById('set-hero-location')) document.getElementById('set-hero-location').value = s.hero_location || '';
      if (document.getElementById('set-availability')) document.getElementById('set-availability').value = s.availability_status || '';
      if (document.getElementById('set-quote-sparkle')) document.getElementById('set-quote-sparkle').value = s.quote_sparkle || '';

      if (document.getElementById('set-stat-years')) document.getElementById('set-stat-years').value = s.stat_years || '';
      if (document.getElementById('set-stat-projects')) document.getElementById('set-stat-projects').value = s.stat_projects || '';
      if (document.getElementById('set-stat-clients')) document.getElementById('set-stat-clients').value = s.stat_clients || '';

      if (document.getElementById('set-contact-email')) document.getElementById('set-contact-email').value = s.contact_email || '';
      if (document.getElementById('set-contact-phone')) document.getElementById('set-contact-phone').value = s.contact_phone || '';
      if (document.getElementById('set-social-fb')) document.getElementById('set-social-fb').value = s.social_facebook || '';
      if (document.getElementById('set-social-ig')) document.getElementById('set-social-ig').value = s.social_instagram || '';
      if (document.getElementById('set-social-gh')) document.getElementById('set-social-gh').value = s.social_github || '';
    }
  } catch (err) {
    console.error('Failed to load settings:', err);
  }
}

/* ==========================================================================
   SKILLS & TOOLSET
   ========================================================================== */
let allSkills = [];

function initSkills() {
  const form = document.getElementById('add-skill-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const name = document.getElementById('skill-name').value.trim();
    const proficiency = document.getElementById('skill-proficiency').value;

    if (!name) return;

    try {
      const res = await fetch('../api/skills.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add', name, proficiency, sort_order: allSkills.length + 1 })
      });
      const data = await res.json();
      if (data.success) {
        showToast('✓ Skill added');
        form.reset();
        await loadSkills();
      }
    } catch (err) {
      console.error(err);
    }
  });
}

async function loadSkills() {
  try {
    const res = await fetch('../api/skills.php');
    const data = await res.json();
    if (data.success) {
      allSkills = data.skills || [];
      document.getElementById('metric-skills-count').textContent = allSkills.length;
      renderSkillsList();
    }
  } catch (err) {
    console.error('Failed to load skills:', err);
  }
}

function renderSkillsList() {
  const container = document.getElementById('skills-list-container');
  if (!container) return;

  if (allSkills.length === 0) {
    container.innerHTML = '<div style="color: var(--admin-text-muted);">No skills added yet.</div>';
    return;
  }

  container.innerHTML = allSkills.map((s) => `
    <div style="background: rgba(255,255,255,0.05); border: 1px solid var(--admin-border); padding: 8px 14px; border-radius: 20px; display: flex; align-items: center; gap: 10px;">
      <span style="font-weight: 600; color: #fff; font-size: 0.85rem;">${escapeHtml(s.name)}</span>
      <span style="font-family: var(--font-mono); font-size: 0.72rem; color: var(--admin-accent);">${escapeHtml(s.proficiency)}</span>
      <button onclick="deleteSkill(${s.id})" style="background: transparent; border: none; color: var(--admin-text-muted); cursor: pointer; font-size: 1rem; line-height: 1; padding: 0 4px;" title="Delete skill">&times;</button>
    </div>
  `).join('');
}

async function deleteSkill(id) {
  if (!confirm('Remove this skill?')) return;

  try {
    const res = await fetch('../api/skills.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete', id })
    });
    const data = await res.json();
    if (data.success) {
      showToast('✓ Skill removed');
      await loadSkills();
    }
  } catch (err) {
    console.error(err);
  }
}

/* ==========================================================================
   ACCOUNT & SECURITY
   ========================================================================== */
function initAccount() {
  const form = document.getElementById('account-form');
  const alertBox = document.getElementById('account-alert');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    alertBox.style.display = 'none';

    const username = document.getElementById('acc-username').value.trim();
    const email = document.getElementById('acc-email').value.trim();
    const current_password = document.getElementById('acc-current-pass').value;
    const new_password = document.getElementById('acc-new-pass').value;

    const btn = document.getElementById('acc-submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<span>Updating...</span>';

    try {
      const res = await fetch('../api/auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update_profile', username, email, current_password, new_password })
      });
      const data = await res.json();

      if (res.ok && data.success) {
        alertBox.className = 'admin-alert alert-success';
        alertBox.textContent = '✓ Profile and credentials updated successfully!';
        alertBox.style.display = 'block';
        document.getElementById('acc-current-pass').value = '';
        document.getElementById('acc-new-pass').value = '';
        showToast('✓ Credentials updated');
      } else {
        alertBox.className = 'admin-alert alert-error';
        alertBox.textContent = data.error || 'Failed to update credentials';
        alertBox.style.display = 'block';
      }
    } catch (err) {
      console.error(err);
      alertBox.className = 'admin-alert alert-error';
      alertBox.textContent = 'Network error during profile update';
      alertBox.style.display = 'block';
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<span>Update Credentials</span><span style="font-size: 1.1rem;">✓</span>';
    }
  });
}

/* ==========================================================================
   LOGOUT & USER ACTIONS
   ========================================================================== */
function initLogout() {
  const btn = document.getElementById('sidebar-logout-btn');
  if (btn) {
    btn.addEventListener('click', async () => {
      if (confirm('Sign out of Admin Dashboard?')) {
        await fetch('../api/auth.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'logout' })
        });
        window.location.href = 'login.php';
      }
    });
  }
}

function initMobileMenu() {
  const toggleBtn = document.getElementById('mobile-toggle-btn');
  const closeBtn = document.getElementById('sidebar-close-btn');
  const sidebar = document.getElementById('admin-sidebar');

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      if (closeBtn) closeBtn.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
    });
  }

  if (closeBtn && sidebar) {
    closeBtn.addEventListener('click', () => {
      sidebar.classList.remove('open');
      closeBtn.style.display = 'none';
    });
  }
}

/* ==========================================================================
   UTILITIES & TOASTS
   ========================================================================== */
function showToast(message) {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.innerHTML = `<span style="color: #ff1e2d; font-size: 1.1rem;">⚡</span> <span>${message}</span>`;
  container.appendChild(toast);

  setTimeout(() => toast.classList.add('show'), 10);
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 400);
  }, 3500);
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
