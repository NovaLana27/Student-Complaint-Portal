// ---------- Mobile menu and Student Portal dropdown ----------
const menuToggle = document.getElementById('menuToggle');
const navLinks = document.getElementById('navLinks');
const portalBtn = document.getElementById('portalBtn');
const portalDropdown = document.getElementById('portalDropdown');

menuToggle.addEventListener('click', function () {
  const isOpen = navLinks.classList.toggle('open');
  menuToggle.innerHTML = isOpen ? '&times;' : '&#9776;';
});

navLinks.querySelectorAll('.nav-link').forEach(function (link) {
  link.addEventListener('click', function () {
    navLinks.classList.remove('open');
    menuToggle.innerHTML = '&#9776;';
  });
});

portalBtn.addEventListener('click', function (event) {
  event.stopPropagation();
  portalDropdown.classList.toggle('open');
});

document.addEventListener('click', function () {
  portalDropdown.classList.remove('open');
});


// ---------- Helpers ----------
function getComplaints() {
  const saved = localStorage.getItem('complaints');
  return saved ? JSON.parse(saved) : [];
}

function saveComplaints(list) {
  localStorage.setItem('complaints', JSON.stringify(list));
}

function generateComplaintId() {
  const year = new Date().getFullYear();
  const random = Math.floor(10000 + Math.random() * 90000);
  return 'RUN-' + year + '-' + random;
}

function showError(inputId, message) {
  const input = document.getElementById(inputId);
  const error = document.getElementById(inputId + 'Error');
  input.closest('.input-wrap').classList.add('invalid');
  error.textContent = message;
}

function clearError(inputId) {
  const input = document.getElementById(inputId);
  const error = document.getElementById(inputId + 'Error');
  input.closest('.input-wrap').classList.remove('invalid');
  error.textContent = '';
}


// ---------- Form validation and submit ----------
const form = document.getElementById('complaintForm');
const successBox = document.getElementById('successBox');
const newComplaintId = document.getElementById('newComplaintId');

const requiredFields = [
  { id: 'fullName', message: 'Please enter your full name.' },
  { id: 'matricNumber', message: 'Please enter your matriculation number.' },
  { id: 'department', message: 'Please select your department.' },
  { id: 'level', message: 'Please select your level.' },
  { id: 'category', message: 'Please select a complaint category.' },
  { id: 'description', message: 'Please describe your complaint.' }
];

requiredFields.forEach(function (field) {
  document.getElementById(field.id).addEventListener('input', function () {
    clearError(field.id);
  });
});

form.addEventListener('submit', function (event) {
  event.preventDefault();
  let isValid = true;

  requiredFields.forEach(function (field) {
    const value = document.getElementById(field.id).value.trim();
    if (value === '') {
      showError(field.id, field.message);
      isValid = false;
    } else {
      clearError(field.id);
    }
  });

  // Matric number format, example: RUN/IFT/24/17397
  const matric = document.getElementById('matricNumber').value.trim();
  const matricPattern = /^[A-Za-z]{3}\/[A-Za-z]{3}\/\d{2}\/\d{3,6}$/;
  if (matric !== '' && !matricPattern.test(matric)) {
    showError('matricNumber', 'Use the format RUN/IFT/24/17397.');
    isValid = false;
  }

  if (!isValid) {
    return;
  }

  const evidenceInput = document.getElementById('evidence');
  const complaint = {
    id: generateComplaintId(),
    fullName: document.getElementById('fullName').value.trim(),
    matricNumber: matric.toUpperCase(),
    department: document.getElementById('department').value,
    level: document.getElementById('level').value,
    category: document.getElementById('category').value,
    description: document.getElementById('description').value.trim(),
    evidence: evidenceInput.files.length > 0 ? evidenceInput.files[0].name : '',
    status: 'Received',
    date: new Date().toLocaleDateString(),
    date: new Date().toLocaleDateString(),
createdAt: new Date().toISOString()

  };

  const complaints = getComplaints();
  complaints.push(complaint);
  saveComplaints(complaints);

  newComplaintId.textContent = complaint.id;
  successBox.hidden = false;
  form.reset();
  successBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
});


// ---------- Track complaint ----------
const trackId = document.getElementById('trackId');
const trackResult = document.getElementById('trackResult');

document.getElementById('checkStatusBtn').addEventListener('click', function () {
  const enteredId = trackId.value.trim().toUpperCase();

  if (enteredId === '') {
    trackResult.textContent = 'Please enter your Complaint ID.';
    return;
  }

  const found = getComplaints().find(function (item) {
    return item.id === enteredId;
  });

  if (found) {
    trackResult.textContent = 'Status: ' + found.status + ' (submitted on ' + found.date + ')';
  } else {
    trackResult.textContent = 'No complaint found with that ID.';
  }
});

