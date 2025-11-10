<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Exam</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
 
  <!-- Tailwind (CDN) -->
  <script src="https://cdn.tailwindcss.com"></script>
 
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
 
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 
  <!-- MathJax -->
  <script>
    window.MathJax = {
      tex : {inlineMath:[['$','$'],['\\(','\\)']], displayMath:[['$$','$$'],['\\[','\\]']]},
      svg : {fontCache:'global'}
    };
  </script>
  <script async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
 
  <style>
    .sidebar-transition{transition:transform .3s ease-in-out}
    .question-nav-btn{transition:all .2s ease}
    .question-nav-btn:hover{transform:scale(1.1)}
    .question-nav-btn.current{transform:scale(1.1);box-shadow:0 0 0 3px rgba(59,130,246,.3)}
    .card-hover{transition:all .3s ease}
    .card-hover:hover{transform:translateY(-2px);box-shadow:0 20px 25px -5px rgba(0,0,0,.1),0 10px 10px -5px rgba(0,0,0,.04)}
    .option-hover{transition:all .2s ease}
    .option-hover:hover{background-color:#f0f9ff;border-color:#e0f2fe}
    .progress-animation{transition:width .8s cubic-bezier(.16,1,.3,1)}
    mjx-container,mjx-container[display="block"],.mjx-chtml{display:inline-block!important}
    mjx-container svg,mjx-container[display="block"] svg,.mjx-chtml svg{vertical-align:middle}
    ::-webkit-scrollbar{width:6px;height:6px}
    ::-webkit-scrollbar-track{background:#f1f5f9;border-radius:3px}
    ::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:3px}
    ::-webkit-scrollbar-thumb:hover{background:#94a3b8}
  </style>
</head>
<body class="bg-gray-50 font-sans text-gray-800 h-screen overflow-hidden">
 
  <!-- ═══ MOBILE HEADER ═══ -->
  <nav class="lg:hidden bg-white shadow-md px-4 py-3 flex items-center justify-between">
    <button class="text-gray-600 hover:text-gray-800 p-2" id="sidebar-toggle">
      <i class="fas fa-bars text-xl"></i>
    </button>
    <div class="flex items-center gap-2">
      <i class="fa-solid fa-clipboard-check text-blue-600"></i>
      <span class="font-bold text-lg" id="exam-title-mobile">Exam</span>
    </div>
    <div class="bg-blue-600 text-white px-3 py-1.5 rounded-lg font-mono font-semibold text-sm" id="exam-timer-mobile">
      <i class="fa-solid fa-clock mr-1"></i>00:00
    </div>
  </nav>
 
  <div class="flex h-full lg:h-screen">
    <!-- ═══ SIDEBAR ═══ -->
    <aside id="question-nav-container" class="sidebar-transition fixed lg:relative inset-y-0 left-0 z-50 w-64 bg-white shadow-xl lg:shadow-lg flex flex-col transform -translate-x-full lg:translate-x-0">
      <button id="sidebar-close" class="lg:hidden self-end p-3 text-gray-500 hover:text-gray-700">
        <i class="fas fa-times text-xl"></i>
      </button>
      <div class="p-6 text-center border-b border-gray-200">
        <h2 class="text-lg font-bold text-gray-800">Question Navigator</h2>
      </div>
      <div class="flex-1 p-4 overflow-y-auto">
        <div id="question-nav" class="grid grid-cols-6 gap-2"></div>
      </div>
      <div class="p-4 border-t border-gray-200">
        <button id="sidebar-submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition shadow-md">
          <i class="fa-solid fa-paper-plane mr-2"></i>Submit Exam
        </button>
      </div>
    </aside>
 
    <div id="sidebar-overlay" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-40 hidden"></div>
 
    <!-- ═══ MAIN CONTENT ═══ -->
    <main class="flex-1 flex flex-col overflow-hidden">
 
      <!-- DESKTOP HEADER -->
      <div class="hidden lg:flex items-center justify-between p-6 bg-white shadow-sm">
        <div class="flex items-center gap-3">
          <i class="fa-solid fa-clipboard-check text-2xl text-blue-600"></i>
          <h1 class="text-2xl font-bold text-gray-800" id="exam-title">Loading Exam…</h1>
        </div>
        <div class="bg-blue-600 text-white px-4 py-2 rounded-lg font-mono font-semibold" id="exam-timer">
          <i class="fa-solid fa-clock mr-2"></i>00:00
        </div>
      </div>
 
      <!-- MOBILE PROGRESS -->
      <div class="lg:hidden p-4 bg-white border-b border-gray-200">
        <div class="mb-2 flex justify-between items-center">
          <span class="text-sm text-gray-600"><i class="fas fa-chart-line mr-2"></i>Completion Progress</span>
          <span class="text-sm font-semibold text-blue-600 mobile-progress-percentage">0%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div id="mobile-progress" class="bg-gradient-to-r from-blue-500 to-cyan-500 h-2 rounded-full progress-animation" style="width:0%"></div>
        </div>
        <div class="mt-2 flex justify-between items-center">
          <span class="text-xs text-gray-500">Questions answered</span>
          <span class="text-xs text-gray-500 mobile-question-counter">0 of 0</span>
        </div>
      </div>
 
      <!-- DESKTOP PROGRESS -->
      <div class="flex-1 overflow-y-auto p-4 lg:p-6">
        <div class="hidden lg:block mb-6">
          <div class="bg-white rounded-xl p-6 shadow-md card-hover">
            <div class="mb-3 flex justify-between items-center">
              <span class="text-gray-600"><i class="fas fa-chart-line mr-2"></i>Completion Progress</span>
              <span class="font-semibold text-blue-600 desktop-progress-percentage">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
              <div id="desktop-progress" class="bg-gradient-to-r from-blue-500 to-cyan-500 h-3 rounded-full progress-animation" style="width:0%"></div>
            </div>
            <div class="mt-3 flex justify-between items-center">
              <span class="text-sm text-gray-500">Questions answered</span>
              <span class="text-sm text-gray-500 desktop-question-counter">0 of 0</span>
            </div>
          </div>
        </div>
 
        <!-- QUESTION CARD -->
        <div id="question-card" class="mb-6"></div>
 
        <!-- NAV BUTTONS -->
        <div class="flex justify-center gap-4 flex-wrap">
          <button id="prev-btn" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-semibold disabled:opacity-50 min-w-32">
            <i class="fa-solid fa-arrow-left mr-2"></i>Previous
          </button>
          <button id="review-btn" class="px-6 py-3 border border-yellow-400 text-yellow-700 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition font-semibold min-w-32">
            <i class="fa-solid fa-flag mr-2"></i>Mark Review
          </button>
          <button id="next-btn" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-semibold shadow-md min-w-32">
            Next<i class="fa-solid fa-arrow-right ml-2"></i>
          </button>
        </div>
      </div>
    </main>
  </div>
 
<script>
/* ───────── 0. BOOTSTRAP (cookie→LS) ───────── */
const getCookie = k => document.cookie.split('; ').find(r=>r.startsWith(k+'='))?.split('=')[1] ?? null;
if(!localStorage.examId){ const eid=getCookie('exam_id'); if(eid) localStorage.examId=eid; }
const EXAM_ID = localStorage.examId;
 
/* ───────── 1. GLOBALS ───────── */
let marks = 0, timerInterval = null;
const answers  = JSON.parse(localStorage.examAnswers  || '{}');
const reviews  = JSON.parse(localStorage.examReviews  || '{}');
const visited  = JSON.parse(localStorage.examVisited  || '{}');
const answeredCorrect = {};
let questions  = [];
let currentIndex = 0;
let examDurationMin = +localStorage.examDuration || 0;
let examMeta = null;
 
/* Element refs */
const $ = id => document.getElementById(id);
const elements = {
  qCard: $('question-card'), qNav: $('question-nav'),
  prev: $('prev-btn'), next: $('next-btn'), review: $('review-btn'),
  timerDesk: $('exam-timer'), timerMob: $('exam-timer-mobile'),
  sToggle: $('sidebar-toggle'), sClose: $('sidebar-close'),
  sOverlay: $('sidebar-overlay'), sCont: $('question-nav-container'),
  submit: $('sidebar-submit')
};
 
/* ───────── 2. DOM READY ───────── */
document.addEventListener('DOMContentLoaded', async () => {
    const requiredSession = sessionStorage.student_id && (sessionStorage.token || sessionStorage.student_token);
  const requiredLocal = localStorage.examId && localStorage.examDuration && localStorage.examTimeLeft;

  if (!requiredSession || !requiredLocal) {
    await Swal.fire({
      icon: 'warning',
      title: 'Missing Data',
      text: 'Essential session or exam data is missing. Please start again.',
      confirmButtonText: 'Go to Home'
    });
    return location.href = '/';
  }
  if(!(sessionStorage.student_id && (sessionStorage.token || sessionStorage.student_token))){
    await Swal.fire({icon:'warning',title:'Session Expired',text:'Please log in again.',confirmButtonText:'Login'});
    return location.href='/student/login';
  }
 
  if(!EXAM_ID){
    await Swal.fire({icon:'error',title:'Missing Exam ID',text:'No exam selected.',confirmButtonText:'Go Back'});
    return location.href='/student/exam';
  }
 
  /* 2-A: fetch exam meta */
  try{
    const meta = await fetch('/api/exam').then(r=>r.json());
    if(meta.success){
      examMeta = meta.exams.find(e=>+e.id===+EXAM_ID);
      examDurationMin = examDurationMin || (examMeta ? parseInt(examMeta.totalTime)||0 : 0);
      localStorage.examDuration = examDurationMin;
    }
  }catch(e){ console.warn('Could not fetch exam meta:',e); }
 
  /* 2-B: fetch questions */
  try{
    const res   = await fetch(`/api/exam/${EXAM_ID}/questions-with-answers`);
    const data  = await res.json();
    questions   = data.questions || [];
    if(!questions.length){
      elements.qCard.innerHTML = '<div class="bg-white rounded-xl p-8 shadow-md text-center"><p class="text-gray-600">No questions available.</p></div>';
      return;
    }
  }catch(e){
    console.error(e); Swal.fire('Error','Failed to load questions.','error'); return;
  }
 
  /* 2-C: titles */
  $('exam-title').textContent = $('exam-title-mobile').textContent =
    examMeta?.examName || 'Exam';
 
  /* 2-D: restore index */
  currentIndex = +localStorage.examCurrentIndex || 0;
  if(currentIndex<0 || currentIndex>=questions.length) currentIndex = 0;
 
  /* 2-E: marks pre-calc & sidebar nav */
  marks = 0;
  questions.forEach((q,idx) => {
    const sel = answers[q.question_id];
    if(Array.isArray(sel)?sel.length:sel!=null){
      const correctIDs = q.answers.filter(a=>a.is_correct).map(a=>a.answer_id).sort();
      const ok = Array.isArray(sel)
                 ? JSON.stringify([...sel].sort()) === JSON.stringify(correctIDs)
                 : sel === correctIDs[0];
      answeredCorrect[q.question_id] = ok;
      if(ok) marks += +q.question_mark;
    }
    const btn = document.createElement('button');
    btn.textContent = idx+1;
    btn.className   = 'w-8 h-8 text-sm font-semibold rounded-full question-nav-btn';
    updateNavBtn(btn, q.question_id, idx);
    btn.onclick = () => { currentIndex = idx; localStorage.examCurrentIndex = idx; renderQuestion(); };
    elements.qNav.appendChild(btn);
  });
 
  /* 2-F: timer */
  let timeLeft = +localStorage.examTimeLeft;
  if(isNaN(timeLeft)||timeLeft<=0){
    timeLeft = examDurationMin * 60;
    localStorage.examTimeLeft = timeLeft;
  }
  const fmt = s=>`${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`;
  const setTimers = t=>{
    elements.timerDesk.innerHTML=`<i class="fa-solid fa-clock mr-2"></i>${t}`;
    elements.timerMob .innerHTML=`<i class="fa-solid fa-clock mr-1"></i>${t}`;
  };
  const tick = ()=>{
    if(timeLeft<=0){clearInterval(timerInterval);timerInterval=null;setTimers('00:00');disableAllInputs();return submitExam(true);}
    setTimers(fmt(timeLeft));timeLeft--;localStorage.examTimeLeft=timeLeft;
  };
  timerInterval=setInterval(tick,1000);tick();
  ['mousemove','click'].forEach(ev=>document.body.addEventListener(ev,()=>{if(!timerInterval)timerInterval=setInterval(tick,1000);}));
 
  /* 2-G: sidebar controls */
  elements.sToggle.onclick=()=>{elements.sCont.classList.remove('-translate-x-full');elements.sOverlay.classList.remove('hidden');document.body.classList.add('overflow-hidden');};
  const closeSide=()=>{elements.sCont.classList.add('-translate-x-full');elements.sOverlay.classList.add('hidden');document.body.classList.remove('overflow-hidden');};
  elements.sClose.onclick=closeSide;elements.sOverlay.onclick=closeSide;
 
  /* 2-H: nav button handlers */
  elements.prev.onclick=()=>{if(currentIndex>0){currentIndex--;renderQuestion();}};
  elements.next.onclick=()=>{if(currentIndex<questions.length-1){currentIndex++;renderQuestion();}else submitExam(false);};
  elements.review.onclick=()=>{const id=questions[currentIndex].question_id;reviews[id]=!reviews[id];localStorage.examReviews=JSON.stringify(reviews);refreshNavBtns();renderQuestion();};
  elements.submit.onclick=()=>submitExam(false);
 
  /* first paint */
  renderQuestion();updateProgress();
});
 
/* ───────── 3. HELPERS ───────── */
function updateProgress(){
  const done=Object.values(answers).filter(v=>Array.isArray(v)?v.length:v!=null).length;
  const pct=questions.length?done/questions.length*100:0;const r=Math.round(pct);
  ['sidebar-progress','mobile-progress','desktop-progress'].forEach(id=>{const e=$(id);if(e)e.style.width=pct+'%';});
  ['progress-percentage','mobile-progress-percentage','desktop-progress-percentage']
    .forEach(cls=>{const e=document.querySelector('.'+cls);if(e)e.textContent=r+'%';});
  ['question-counter','mobile-question-counter','desktop-question-counter']
    .forEach(cls=>{const e=document.querySelector('.'+cls);if(e)e.textContent=`${done} of ${questions.length}`;});
}
function updateNavBtn(btn,qid,idx){
  btn.className='w-8 h-8 text-sm font-semibold rounded-full question-nav-btn';
  if(idx===currentIndex) return btn.classList.add('bg-blue-600','text-white','border-blue-600','current');
  if(reviews[qid])       return btn.classList.add('bg-yellow-500','text-white','border-yellow-500');
  const sel=answers[qid];if(Array.isArray(sel)?sel.length:sel!=null)return btn.classList.add('bg-green-500','text-white','border-green-500');
  if(visited[qid])       return btn.classList.add('bg-red-500','text-white','border-red-500');
  btn.classList.add('bg-white','text-gray-700','border-gray-300','hover:bg-gray-50');
}
const refreshNavBtns=()=>[...elements.qNav.children].forEach((b,i)=>updateNavBtn(b,questions[i].question_id,i));
const disableAllInputs=()=>{elements.prev.disabled=elements.next.disabled=elements.review.disabled=true;[...elements.qNav.children].forEach(b=>b.disabled=true);elements.qCard.querySelectorAll('input').forEach(i=>i.disabled=true);};
const isMulti=q=>!!q.has_multiple_correct_answer;
 
/* ───────── 4. RENDER QUESTION ───────── */
function renderQuestion(){
  localStorage.examCurrentIndex=currentIndex;
  const q=questions[currentIndex];
  visited[q.question_id]=true;localStorage.examVisited=JSON.stringify(visited);refreshNavBtns();
 
  let html=`<div class="bg-white rounded-xl shadow-md card-hover"><div class="p-6 lg:p-8"><h2 id="q-title" class="text-xl lg:text-2xl font-bold mb-4">Q${currentIndex+1}: ${q.question_title}</h2><div class="space-y-3">`;
  const multi=isMulti(q);
  q.answers.forEach(a=>{
    html+=`<div class="option-hover border border-gray-200 rounded-lg p-4 cursor-pointer">
      <label class="flex items-center cursor-pointer">
        <input class="w-5 h-5 text-blue-600 border-gray-300 ${multi?'':'rounded-full'} focus:ring-2 mr-4"
               type="${multi?'checkbox':'radio'}" name="q_${q.question_id}${multi?'[]':''}"
               id="ans_${a.answer_id}" value="${a.answer_id}">
        <span class="flex-grow" id="lbl_${a.answer_id}">${a.answer_title}</span>
      </label></div>`;
  });
  html+='</div></div></div>';
  elements.qCard.innerHTML=html;
 
  MathJax.typesetPromise([$('q-title')]);
  q.answers.forEach(a=>MathJax.typesetPromise([$('lbl_'+a.answer_id)]));
 
  if(answers[q.question_id]!=null){
    (Array.isArray(answers[q.question_id])?answers[q.question_id]:[answers[q.question_id]]).forEach(id=>{const e=$('ans_'+id);if(e)e.checked=true;});
  }
 
  elements.qCard.querySelectorAll(`input[name^="q_${q.question_id}"]`).forEach(inp=>inp.onchange=e=>{
    const id=+e.target.value,was=!!answeredCorrect[q.question_id];
    if(multi){
      answers[q.question_id]=Array.isArray(answers[q.question_id])?answers[q.question_id]:[];
      e.target.checked?answers[q.question_id].push(id):answers[q.question_id]=answers[q.question_id].filter(x=>x!==id);
      answers[q.question_id].sort();
    }else{
      answers[q.question_id]=id;
    }
    const correct=q.answers.filter(a=>a.is_correct).map(a=>a.answer_id).sort();
    const now=multi
               ? JSON.stringify(answers[q.question_id])===JSON.stringify(correct)
               : id===correct[0];
    if(now&&!was)marks+=+q.question_mark;
    if(!now&&was)marks-=+q.question_mark;
    answeredCorrect[q.question_id]=now;
    localStorage.examAnswers=JSON.stringify(answers);
    updateProgress();refreshNavBtns();
  });
 
  elements.review.innerHTML=reviews[q.question_id]
     ?'<i class="fa-solid fa-flag mr-2"></i>Unmark Review'
     :'<i class="fa-solid fa-flag mr-2"></i>Mark Review';
  elements.prev.disabled=currentIndex===0;
  elements.next.innerHTML=currentIndex<questions.length-1
     ?'Next<i class="fa-solid fa-arrow-right ml-2"></i>'
     :'Submit<i class="fa-solid fa-paper-plane ml-2"></i>';
}
 
/* ───────── 5. SUBMIT EXAM ───────── */
async function submitExam(auto = false) {
  if (timerInterval) {
    clearInterval(timerInterval);
    timerInterval = null;
  }

  // Recalculate marks
  let calc = 0;
  Object.entries(answers).forEach(([qid, sel]) => {
    const q = questions.find(x => x.question_id === +qid);
    if (!q) return;
    const correct = q.answers.filter(a => a.is_correct).map(a => a.answer_id).sort();
    const ok = Array.isArray(sel)
      ? JSON.stringify([...sel].sort()) === JSON.stringify(correct)
      : sel === correct[0];
    if (ok) calc += +q.question_mark;
  });

  if (calc !== marks) {
    return Swal.fire('Error', 'Marks calculation mismatch.', 'error');
  }

  if (!auto) {
    const ok1 = (await Swal.fire({
      title: 'Submit exam?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes'
    })).isConfirmed;
    if (!ok1) return;

    const ok2 = (await Swal.fire({
      title: 'Final confirmation',
      text: 'Once submitted, answers cannot be changed.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Submit'
    })).isConfirmed;
    if (!ok2) return;
  }

  Swal.fire({ title: 'Submitting…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

  const formattedAnswers = Object.entries(answers).map(([questionId, selected]) => ({
    question_id: Number(questionId),
    selected: selected ?? null
  }));

  try {
    // Step 1: Submit exam
    const response = await fetch(`/api/exam/${EXAM_ID}/submit`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
      },
      body: JSON.stringify({ 
        student_id: Number(sessionStorage.student_id), 
        answers: formattedAnswers 
      })
    });

    const result = await response.json();
    if (!response.ok || !result.success) throw new Error(result.message || 'Submission failed');

    // Clean localStorage after successful submission
    ['examAnswers', 'examCurrentIndex', 'examReviews', 'examVisited', 'examEndTime',
      'examId', 'examDuration', 'examTimeLeft', 'examTitle'].forEach(k => localStorage.removeItem(k));

    Swal.close();

    // Step 2: Fetch student details to check for email
    const detailsResponse = await fetch('/api/student/details', {
      headers: {
        'Authorization': `Bearer ${sessionStorage.student_token || sessionStorage.token}`,
        'Content-Type': 'application/json'
      }
    });

    const detailsData = await detailsResponse.json();
    
    if (detailsData.success && detailsData.student && detailsData.student.email) {
      // If email exists in student details, redirect to result page
      location.href = '/student/result';
    } else {
      // If no email exists, redirect to get result via email page
      location.href = `/result/by-email?exam_id=${EXAM_ID}`;
    }

  } catch (e) {
    console.error(e);
    Swal.close();
    Swal.fire('Error', e.message || 'Submission failed.', 'error');
  }
}


window.submitExam=submitExam;
</script>
</body>
</html>