// ---------- Storage helpers ----------
const STORAGE_KEY = 'complaints';
const PAGE_SIZE = 5;
const OVERDUE_DAYS = 7;

function getComplaints() {
  const saved = localStorage.getItem(STORAGE_KEY);
  return saved ? JSON.parse(saved) : [];
}

function saveComplaints(list) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
}

// Adds 3 sample complaints the first time, so the admin pages are not empty.
// Delete this function (and the call below) once real complaints come in.
function seedIfEmpty() {
  if (getComplaints().length > 0) {
    return;
  }

  saveComplaints([
    {
      id: 'RUN-2026-00122',
      fullName: 'Blessing Okafor',
      matricNumber: 'RUN/IFT/24/16888',
      department: 'Computer Science',
      level: '300 Level',
      category: 'Library',
      description: 'The library Wi-Fi has been down for two weeks.',
      evidence: '',
      status: 'Resolved',
      response: 'The Wi-Fi has been restored.',
      createdAt: '2026-11-09T09:00:00'
    },
    {
      id: 'RUN-2026-00123',
      fullName: 'Temitope Adebayo',
      matricNumber: 'RUN/IFT/24/17001',
      department: 'Software Engineering',
      level: '200 Level',
      category: 'Hostel',
      description: 'The water supply in my hostel block is unreliable.',
      evidence: '',
      status: 'In Progress',
      response: '',
      createdAt: '2026-11-10T11:30:00'
    },
    {
      id: 'RUN-2026-00124',
      fullName: 'Ifeanyi Oseji',
      matricNumber: 'RUN/IFT/24/17397',
      department: 'Information Technology',
      level: '300 Level',
      category: 'Academic',
      description: 'My CA score is missing from the portal.',
      evidence: '',
      status: 'Pending',
      response: '',
      createdAt: '2026-11-10T14:15:00'
    }
  ]);
}


// ---------- Small helpers ----------
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text == null ? '' : text;
  return div.innerHTML;
}

// The student form saves "Received"; the admin side calls that "Pending".
function cleanStatus(status) {
  return status === 'Received' ? 'Pending' : status;
}

function getDate(complaint) {
  return complaint.createdAt ? new Date(complaint.createdAt) : new Date(complaint.date);
}

function formatDate(complaint) {
  const date = getDate(complaint);
  if (isNaN(date)) {
    return complaint.date || '';
  }
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function isOverdue(complaint) {
  if (cleanStatus(complaint.status) === 'Resolved') {
    return false;
  }
  const ageInDays = (Date.now() - getDate(complaint)) / 86400000;
  return ageInDays > OVERDUE_DAYS;
}

function shortId(id) {
  return '#' + String(id).split('-').pop().replace(/^0+/, '');
}

function badgeFor(complaint) {
  const status = cleanStatus(complaint.status);
  if (status === 'Resolved') {
    return '<span class="badge badge-resolved">Resolved</span>';
  }
  if (status === 'In Progress') {
    return '<span class="badge badge-progress">In Progress</span>';
  }
  if (isOverdue(complaint)) {
    return '<span class="badge badge-overdue">Overdue</span>';
  }
  return '<span class="badge badge-pending">Pending</span>';
}

function sortedNewestFirst(list) {
  return list.slice().sort(function (a, b) {
    return getDate(b) - getDate(a);
  });
}


// ---------- Sidebar toggle (both pages) ----------
const hamburger = document.getElementById('hamburger');
const sidebar = document.getElementById('sidebar');

hamburger.addEventListener('click', function () {
  sidebar.classList.toggle('open');
});


// ---------- Dashboard page ----------
function renderDashboard() {
  const complaints = getComplaints();

  const resolved = complaints.filter(function (c) {
    return cleanStatus(c.status) === 'Resolved';
  }).length;

  const overdue = complaints.filter(isOverdue).length;
  const pending = complaints.length - resolved - overdue;

  document.getElementById('statTotal').textContent = complaints.length;
  document.getElementById('statPending').textContent = pending;
  document.getElementById('statResolved').textContent = resolved;
  document.getElementById('statOverdue').textContent = overdue;

  const body = document.getElementById('recentBody');
  const recent = sortedNewestFirst(complaints).slice(0, 5);

  body.innerHTML = recent.map(function (c) {
    return '<tr>' +
      '<td class="id-cell">' + escapeHtml(shortId(c.id)) + '</td>' +
      '<td><span class="student-name">' + escapeHtml(c.fullName) + '</span>' +
      '<span class="student-matric">(' + escapeHtml(c.matricNumber) + ')</span></td>' +
      '<td>' + escapeHtml(c.category) + '</td>' +
      '<td>' + badgeFor(c) + '</td>' +
      '<td>' + escapeHtml(formatDate(c)) + '</td>' +
      '</tr>';
  }).join('');
}


// ---------- Manage complaints page ----------
let currentPage = 1;
let openComplaintId = null;

function getFilteredComplaints() {
  const search = document.getElementById('searchInput').value.trim().toLowerCase();
  const filter = document.getElementById('statusFilter').value;

  return sortedNewestFirst(getComplaints()).filter(function (c) {
    const matchesSearch =
      c.id.toLowerCase().includes(search) ||
      c.matricNumber.toLowerCase().includes(search);

    let matchesFilter = true;
    if (filter === 'Overdue') {
      matchesFilter = isOverdue(c);
    } else if (filter !== 'All') {
      matchesFilter = cleanStatus(c.status) === filter && !isOverdue(c);
    }

    return matchesSearch && matchesFilter;
  });
}

function renderComplaints() {
  const list = getFilteredComplaints();
  const totalPages = Math.max(1, Math.ceil(list.length / PAGE_SIZE));

  if (currentPage > totalPages) {
    currentPage = totalPages;
  }

  const start = (currentPage - 1) * PAGE_SIZE;
  const pageItems = list.slice(start, start + PAGE_SIZE);

  document.getElementById('emptyMessage').hidden = list.length > 0;

  document.getElementById('complaintsBody').innerHTML = pageItems.map(function (c) {
    return '<tr>' +
      '<td class="id-cell">' + escapeHtml(shortId(c.id)) + '</td>' +
      '<td><span class="student-name">' + escapeHtml(c.fullName) + '</span>' +
      '<span class="student-matric">(' + escapeHtml(c.matricNumber) + ')</span></td>' +
      '<td>' + escapeHtml(c.category) + '</td>' +
      '<td>' + badgeFor(c) + '</td>' +
      '<td>' + escapeHtml(formatDate(c)) + '</td>' +
      '<td><button class="view-btn" data-id="' + escapeHtml(c.id) + '" aria-label="View complaint">' +
      '<svg width="18" height="18"><use href="#i-eye"/></svg></button></td>' +
      '</tr>';
  }).join('');

  renderPagination(totalPages);
}

function renderPagination(totalPages) {
  const box = document.getElementById('pagination');
  let html = '<button class="page-btn" data-page="prev"' + (currentPage === 1 ? ' disabled' : '') + '>&lsaquo; Previous</button>';

  for (let i = 1; i <= totalPages; i++) {
    html += '<button class="page-btn' + (i === currentPage ? ' active' : '') + '" data-page="' + i + '">' + i + '</button>';
  }

  html += '<button class="page-btn" data-page="next"' + (currentPage === totalPages ? ' disabled' : '') + '>Next &rsaquo;</button>';
  box.innerHTML = html;
}

function openModal(id) {
  const complaint = getComplaints().find(function (c) {
    return c.id === id;
  });

  if (!complaint) {
    return;
  }

  openComplaintId = id;

  document.getElementById('mId').textContent = shortId(complaint.id);
  document.getElementById('mName').textContent = complaint.fullName;
  document.getElementById('mMatric').textContent = complaint.matricNumber;
  document.getElementById('mDept').textContent = complaint.department;
  document.getElementById('mLevel').textContent = complaint.level;
  document.getElementById('mCategory').textContent = complaint.category;
  document.getElementById('mDate').textContent = formatDate(complaint);
  document.getElementById('mEvidence').textContent = complaint.evidence || 'None attached';
  document.getElementById('mDesc').textContent = complaint.description;
  document.getElementById('mStatus').value = cleanStatus(complaint.status);
  document.getElementById('mResponse').value = complaint.response || '';

  document.getElementById('modal').hidden = false;
}

function closeModal() {
  document.getElementById('modal').hidden = true;
  openComplaintId = null;
}

function saveModal() {
  const complaints = getComplaints();
  const complaint = complaints.find(function (c) {
    return c.id === openComplaintId;
  });

  if (complaint) {
    complaint.status = document.getElementById('mStatus').value;
    complaint.response = document.getElementById('mResponse').value.trim();
    saveComplaints(complaints);
  }

  closeModal();
  renderComplaints();
}

function setupComplaintsPage() {
  document.getElementById('searchInput').addEventListener('input', function () {
    currentPage = 1;
    renderComplaints();
  });

  document.getElementById('statusFilter').addEventListener('change', function () {
    currentPage = 1;
    renderComplaints();
  });

  document.getElementById('complaintsBody').addEventListener('click', function (event) {
    const button = event.target.closest('.view-btn');
    if (button) {
      openModal(button.dataset.id);
    }
  });

  document.getElementById('pagination').addEventListener('click', function (event) {
    const button = event.target.closest('.page-btn');
    if (!button || button.disabled) {
      return;
    }

    const value = button.dataset.page;
    if (value === 'prev') {
      currentPage--;
    } else if (value === 'next') {
      currentPage++;
    } else {
      currentPage = Number(value);
    }
    renderComplaints();
  });

  document.getElementById('modalClose').addEventListener('click', closeModal);
  document.getElementById('modalCancel').addEventListener('click', closeModal);
  document.getElementById('modalSave').addEventListener('click', saveModal);

  document.getElementById('modal').addEventListener('click', function (event) {
    if (event.target.id === 'modal') {
      closeModal();
    }
  });

  renderComplaints();
}


// ---------- Start ----------
seedIfEmpty();

if (document.getElementById('recentBody')) {
  renderDashboard();
}

if (document.getElementById('complaintsBody')) {
  setupComplaintsPage();
}