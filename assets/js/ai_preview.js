// assets/js/ai_preview.js - Real-Time Dynamic Prioritization Preview Engine (DPT-RRT)

document.addEventListener('DOMContentLoaded', () => {
  const titleInput = document.getElementById('concernTitle');
  const descInput = document.getElementById('concernDescription');
  const categorySelect = document.getElementById('concernCategory');
  
  const previewBox = document.getElementById('aiPreviewBox');
  const scoreBadge = document.getElementById('aiScoreBadge');
  const priorityBadge = document.getElementById('aiPriorityBadge');
  const slaBadge = document.getElementById('aiSlaBadge');
  const scoreFill = document.getElementById('aiScoreFill');
  const reasonsList = document.getElementById('aiReasonsList');
  const rrtNotice = document.getElementById('aiRrtNotice');

  if (!titleInput || !descInput || !previewBox) {
    return;
  }

  // Critical hazard keywords
  const criticalKeywords = [
    'exposed wire', 'exposed wiring', 'wire', 'spark', 'sparks', 'sparking',
    'fire', 'smoke', 'shock', 'electric', 'short circuit', 'electrocution',
    'water leak', 'leak near', 'flooding', 'collapse', 'ceiling collapsed',
    'medical emergency', 'injured', 'injury', 'bleeding', 'explosion', 'toxic',
    'gas leak', 'danger', 'hazard', 'immediate danger', 'life-threatening'
  ];

  // High urgency keywords
  const highKeywords = [
    'broken chair', 'broken chairs', 'projector not working', 'projector', 'hdmi broken',
    'exam', 'examination', 'midterm', 'final exam', 'deadline today', 'deadline tomorrow',
    'cannot enroll', 'enrollment locked', 'portal error', 'missing grade', 'graduation requirement',
    'aircon not cooling', 'extreme heat', 'no power', 'blackout in lab', 'server down',
    'lab unusable', 'unsafe'
  ];

  // Medium urgency keywords
  const mediumKeywords = [
    'internet', 'wifi', 'wi-fi', 'slow connection', 'comfort room', 'restroom dirty',
    'no water', 'sanitation', 'canteen food', 'library book', 'card error', 'printer jam',
    'attendance error', 'schedule conflict', 'clarification'
  ];

  function evaluateText() {
    const text = (titleInput.value + ' ' + descInput.value).toLowerCase().trim();
    if (text.length < 5) {
      previewBox.classList.remove('active');
      return;
    }

    previewBox.classList.add('active');

    let score = 25;
    let reasons = [];
    let detectedCritical = [];
    let detectedHigh = [];
    let detectedMedium = [];

    // Category boost
    const selectedCat = categorySelect ? categorySelect.options[categorySelect.selectedIndex]?.text?.toLowerCase() || '' : '';
    if (selectedCat.includes('facilities')) score += 15;
    else if (selectedCat.includes('academic')) score += 10;
    else if (selectedCat.includes('service')) score += 5;

    criticalKeywords.forEach(kw => {
      if (text.includes(kw)) detectedCritical.push(kw);
    });
    highKeywords.forEach(kw => {
      if (text.includes(kw)) detectedHigh.push(kw);
    });
    mediumKeywords.forEach(kw => {
      if (text.includes(kw)) detectedMedium.push(kw);
    });

    if (detectedCritical.length > 0) {
      score = Math.max(score, 85) + (detectedCritical.length * 4);
      reasons.push("Critical Hazard Detected: " + detectedCritical.slice(0, 3).join(', '));
    } else if (detectedHigh.length > 0) {
      score = Math.max(score, 65) + (detectedHigh.length * 3);
      reasons.push("High Impact Detected: " + detectedHigh.slice(0, 3).join(', '));
    } else if (detectedMedium.length > 0) {
      score = Math.max(score, 45) + (detectedMedium.length * 2);
      reasons.push("Service Issues Detected: " + detectedMedium.slice(0, 3).join(', '));
    } else {
      reasons.push("Standard / Routine priority");
    }

    score = Math.min(100, Math.max(1, score));

    let priority = 'Low';
    let sla = '120 Hours (5 Days)';
    let color = '#64748b';
    let isRrt = false;

    if (score >= 80) {
      priority = 'Critical';
      sla = '24 Hours (Immediate)';
      color = '#dc2626';
      isRrt = true;
    } else if (score >= 60) {
      priority = 'High';
      sla = '48 Hours (2 Days)';
      color = '#ea580c';
    } else if (score >= 40) {
      priority = 'Medium';
      sla = '72 Hours (3 Days)';
      color = '#d97706';
    } else {
      priority = 'Low';
      sla = '120 Hours (5 Days)';
      color = '#10b981';
    }

    // Update DOM elements
    if (scoreBadge) scoreBadge.textContent = score + '/100';
    if (priorityBadge) {
      priorityBadge.textContent = priority;
      priorityBadge.className = 'priority-pill priority-' + priority.toLowerCase();
    }
    if (slaBadge) slaBadge.textContent = 'Resolution Target SLA: ' + sla;
    if (scoreFill) {
      scoreFill.style.width = score + '%';
      scoreFill.style.background = color;
    }
    if (reasonsList) {
      reasonsList.innerHTML = reasons.map(r => `<li>${r}</li>`).join('');
    }
    if (rrtNotice) {
      rrtNotice.style.display = isRrt ? 'flex' : 'none';
    }
  }

  titleInput.addEventListener('input', evaluateText);
  descInput.addEventListener('input', evaluateText);
  if (categorySelect) categorySelect.addEventListener('change', evaluateText);
});
