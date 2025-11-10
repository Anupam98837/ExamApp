<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Manage Questions</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Bootstrap & deps -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <link rel="stylesheet" href="{{ asset('css/pages/question/manageQuestion.css') }}"/>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <button id="sidebarToggle" class="sidebar-toggle"><i class="fas fa-bars"></i></button>
    <div class="sidebar-overlay"></div>
    <main class="col-md-12 ms-sm-auto px-md-4 py-4 main-content">
      <!-- breadcrumb -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-4">
        <ol class="breadcrumb nav-breadcrumb mb-0">
          <li class="breadcrumb-item">
            <a href="{{ url('/exam/manage') }}">
              <i class="fas fa-file-alt me-1"></i><span id="examName">Exam</span>
            </a>
          </li>
          <li class="breadcrumb-item active">Questions</li>
        </ol>
        <div class="d-none d-sm-flex align-items-center gap-2 px-3 py-2 bg-white rounded-3 text-muted">
          <i class="far fa-calendar-alt"></i>
          <span id="currentDate"></span>
        </div>
      </div>

      <div class="row">
        <!-- sidebar -->
        <div class="col-lg-3 mb-4 questions-sidebar">
          <div class="glass-effect rounded-3 shadow-sm h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
              <h5 class="fw-semibold mb-0"><i class="fas fa-list-ul me-2"></i>Questions</h5>
              <div class="d-flex gap-2">
                <button id="btnNewQ" class="btn btn-sm gradient-btn text-white"><i class="fas fa-plus me-1"></i>New</button>
                <button id="closeSidebar" class="btn btn-sm btn-outline-secondary d-lg-none"><i class="fas fa-times"></i></button>
              </div>
            </div>
            <div class="p-3 border-bottom">
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                <input id="searchInput" class="form-control" placeholder="Search questions…">
              </div>
            </div>
            <div class="flex-grow-1 overflow-auto">
              <ul id="qList" class="list-group list-group-flush">
                <li class="list-group-item text-center py-4 text-muted">
                  <i class="fas fa-spinner fa-spin me-2"></i>Loading…
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- editor -->
        <div class="col-lg-9">
          <div class="glass-effect rounded-3 shadow-sm p-4">
            <form id="qForm" class="needs-validation" novalidate>
              <input type="hidden" id="qId">
              <div class="row g-3 mb-4">
                <div class="col-md-4">
                  <label class="form-label" for="qType">Question Type</label>
                  <select id="qType" class="form-select" required>
                    <option value="multiple_choice">Multiple Choice</option>
                    <option value="single_choice">Single Choice</option>
                    <option value="true_false">True / False</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label" for="qMarks">Marks</label>
                  <input id="qMarks" class="form-control" type="number" min="1" value="1" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label" for="qOrder">Display Order</label>
                  <input id="qOrder" class="form-control" type="number" min="1" value="1" required>
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label" for="qTitle">Question Title</label>
                <textarea id="qTitle" class="summernote" required></textarea>
              </div>
              <div class="mb-4">
                <label class="form-label" for="qDesc">Description (optional)</label>
                <textarea id="qDesc" class="summernote"></textarea>
              </div>
              <div class="mb-4">
                <label class="form-label" for="qExplain">Answer Explanation (optional)</label>
                <textarea id="qExplain" class="summernote"></textarea>
              </div>

              <!-- answers -->
              <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="fw-semibold mb-0">Answers</h5>
                  <button id="addAnsBtn" class="btn btn-sm btn-outline-primary" type="button">
                    <i class="fas fa-plus me-1"></i>Add Answer
                  </button>
                </div>
                <div id="ansContainer" class="d-flex flex-column gap-3"></div>
              </div>

              <div class="d-flex justify-content-end gap-3 pt-4 border-top">
                <button id="btnCancel" class="btn btn-outline-secondary" type="button">Cancel</button>
                <button id="btnSave" class="btn gradient-btn text-white" type="submit">
                  <i class="fas fa-save me-1"></i>Save Question
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.js"></script>

<script>
/* ---------------- init helpers ---------------- */
$('#currentDate').text(new Date().toLocaleDateString('en-US',{weekday:'short',month:'short',day:'numeric'}));
const token = localStorage.getItem('token') || sessionStorage.getItem('token');
const examId = new URLSearchParams(location.search).get('exam_id');
if(!examId){Swal.fire('Error','Missing exam_id','error');throw 'param';}

/* summernote config */
const sOpts = {height:120,toolbar:[
  ['style',['bold','italic','underline','clear']],
  ['para',['ul','ol','paragraph']],
  ['insert',['link','picture','video','hr']],
  ['view',['codeview']]
]};
$('#qTitle,#qDesc,#qExplain').summernote(sOpts);

/* exam name */

fetch(`/api/exam/${examId}`, {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json',
    // Passport/Sanctum/etc. will look for this
  },
  // If you’re using cookie‐based auth (Laravel Sanctum),
  // you’ll need credentials too:
  // credentials: 'include'
})
  .then(res => {
    if (res.status === 401) {
      // you’re still not authorized
      throw new Error('Unauthorized');
    }
    return res.json();
  })
  .then(data => {
    const name = data.success && data.exam
      ? data.exam.examName
      : `Exam #${examId}`;
    $('#examName').text(name);
  })
  .catch(err => {
    console.error(err);
    $('#examName').text(`Exam #${examId}`);
  });



/* ---------------- list handling ---------------- */
let editingId=null, questions=[];
function truncate(h){return $('<div>').html(h).text().slice(0,60)||'Untitled';}

function renderList(list){
  if(!list.length){
    $('#qList').html('<li class="list-group-item text-center py-4 text-muted"><i class="fas fa-question-circle me-2"></i>No questions yet</li>');
    return;
  }
  $('#qList').html(list.map(q=>`
    <li class="list-group-item question-list-item ${editingId===q.question_id?'active':''}" data-id="${q.question_id}">
      <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center text-truncate pe-2" style="flex:1;min-width:0">
          <span class="badge bg-light text-dark me-2">${q.question_order}</span>
          <span class="text-truncate">${truncate(q.question_title)}</span>
        </div>
        <div class="d-flex gap-2">
          <button class="btn-edit btn btn-sm btn-outline-primary p-1"><i class="fas fa-edit fa-sm"></i></button>
          <button class="btn-del  btn btn-sm btn-outline-danger  p-1"><i class="fas fa-trash-alt fa-sm"></i></button>
        </div>
      </div>
    </li>`).join(''));
}

async function loadList() {
  // show loading spinner
  $('#qList').html(
    '<li class="list-group-item text-center py-4 text-muted">' +
    '<i class="fas fa-spinner fa-spin me-2"></i>Loading…' +
    '</li>'
  );

  // fetch with Authorization header
  const r = await fetch(`/api/questions/view?exam_id=${examId}`, {
    headers: {
      'Accept':        'application/json',
      'Authorization': `Bearer ${token}`
    }
  });

  // redirect on unauthorized
  if (r.status === 401 || r.status === 403) {
    return window.location.href = '/unauthorized';
  }

  // parse JSON and render
  const j = await r.json();
  questions = j.success ? j.data : [];
  renderList(questions);
}


/* ---------------- answer blocks ---------------- */
function answerBlock(d={}){
  const t=$('#qType').val();
  const single=t==='single_choice'||t==='true_false';
  $('#ansContainer').append(`
    <div class="answer-block p-3 rounded border ${d.is_correct?'correct':''}">
      <div class="d-flex align-items-start gap-3">
        <div class="d-flex flex-column align-items-center pt-1">
          <input class="ans-correct form-check-input"
                 type="${single?'radio':'checkbox'}"
                 name="${t==='true_false'?'trueFalse':'answerCorrect'}"
                 ${d.is_correct?'checked':''}>
        </div>
        <div class="flex-grow-1 w-100"> <!-- Added wrapper div with w-100 -->
          <textarea class="summernote ans-text w-100"></textarea>
        </div>
      </div>
      <button type="button" class="btn-delAns btn btn-sm btn-link text-danger p-0"><i class="fas fa-trash-alt"></i></button>
    </div>`);
    
  $('#ansContainer .answer-block:last .summernote')
    .summernote(sOpts)
    .summernote('code',d.answer_title||'');
}
$('#addAnsBtn').on('click',()=>answerBlock());
$('#ansContainer').on('click','.btn-delAns',function(){$(this).closest('.answer-block').remove();});

/* ---------------- populate / reset ---------------- */
async function populateEditor(id) {
  // fetch with Bearer token and accept header
  const r = await fetch(`/api/questions/view/${id}`, {
    headers: {
      'Accept':        'application/json',
      'Authorization': `Bearer ${token}`
    }
  });

  // redirect if unauthorized
  if (r.status === 401 || r.status === 403) {
    return window.location.href = '/unauthorized';
  }

  // parse JSON
  const j = await r.json();
  if (!j.success) {
    Swal.fire('Error', j.message, 'error');
    return;
  }

  // now prefill form
  const q = j.data;
  editingId = q.question_id;

  const uiType = q.question_type === 'mcq' ? 'multiple_choice' : q.question_type;
  $('#qType').val(uiType).trigger('change');
  $('#qMarks').val(q.question_mark);
  $('#qOrder').val(q.question_order);
  $('#qTitle').summernote('code', q.question_title);
  $('#qDesc').summernote('code', q.question_description || '');
  $('#qExplain').summernote('code', q.answer_explanation || '');

  $('#ansContainer').empty();
  q.answers.forEach(a => answerBlock(a));

  $('.question-list-item').removeClass('active');
  $(`#qList li[data-id="${id}"]`).addClass('active');
  window.scrollTo({ top: 0, behavior: 'smooth' });
  $('body').removeClass('sidebar-open');
}


function resetForm(){
  editingId=null;
  $('#qForm')[0].reset();

  // clear Summernote contents **thoroughly**
  ['#qTitle','#qDesc','#qExplain'].forEach(sel=>{
    $(sel).summernote('code','');   // clear HTML
    // trick Summernote into re-rendering a blank editor
    $(sel).next('.note-editor').find('.note-editable').html('<p><br/></p>');
  });

  $('#ansContainer').empty();
  if($('#qType').val()==='true_false'){
    answerBlock({answer_title:'True'});answerBlock({answer_title:'False'});
    $('#addAnsBtn').hide();
  }else{answerBlock();answerBlock();$('#addAnsBtn').show();}
  $('.question-list-item').removeClass('active');
  window.scrollTo({top:0,behavior:'smooth'});$('body').removeClass('sidebar-open');
}
$('#btnNewQ,#btnCancel').on('click',resetForm);

/* ---------------- search & sidebar ---------------- */
$('#searchInput').on('input',function(){
  const t=$(this).val().toLowerCase();
  $('#qList li').each(function(){$(this).toggle($(this).text().toLowerCase().includes(t));});
});
$('#sidebarToggle').on('click',()=>$('body').addClass('sidebar-open'));
$('#closeSidebar,.sidebar-overlay').on('click',()=>$('body').removeClass('sidebar-open'));

/* ---------------- delete ---------------- */
$('#qList').on('click','.btn-del', function(e) {
  e.stopPropagation();
  const id = $(this).closest('li').data('id');

  Swal.fire({
    title: 'Delete?',
    text: 'This cannot be undone',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444'
  })
  .then(async res => {
    if (!res.isConfirmed) return;

    // send DELETE with CSRF and Bearer token
    const r = await fetch(`/api/questions/delete/${id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    });

    // redirect on unauthorized
    if (r.status === 401 || r.status === 403) {
      return window.location.href = '/unauthorized';
    }

    const j = await r.json();
    if (j.success) {
      Swal.fire('Deleted!', '', 'success');
      await loadList();
      resetForm();
    } else {
      Swal.fire('Error', j.message, 'error');
    }
  });
});


/* edit */
$('#qList').on('click','.btn-edit',function(e){
  e.stopPropagation();
  populateEditor($(this).closest('li').data('id'));
});

/* type change */
$('#qType').on('change',function(){
  if($(this).val()==='true_false'){
    $('#ansContainer').empty();
    answerBlock({answer_title:'True'});answerBlock({answer_title:'False'});
    $('#addAnsBtn').hide();
  }else{$('#addAnsBtn').show();
    $('#ansContainer .ans-correct').attr('type',$(this).val()==='single_choice'?'radio':'checkbox');
  }
});

/* ---------------- save ---------------- */
$('#qForm').on('submit', async function(e) {
  e.preventDefault();
  if (!this.checkValidity()) {
    this.classList.add('was-validated');
    return;
  }

  const body = {
    exam_id: +examId,
    question_title:       $('#qTitle').summernote('code'),
    question_description: $('#qDesc').summernote('code'),
    answer_explanation:   $('#qExplain').summernote('code'),
    question_type:        $('#qType').val(),
    question_mark:        +$('#qMarks').val(),
    question_order:       +$('#qOrder').val(),
    answers: []
  };

  let atLeastOneCorrect = false;
  $('#ansContainer .answer-block').each(function(i) {
    const html = $(this).find('.ans-text').summernote('code').trim();
    if (!$('<div>').html(html).text().trim()) return;  // skip blank
    const correct = $(this).find('.ans-correct').is(':checked');
    if (correct) atLeastOneCorrect = true;
    body.answers.push({
      answer_title: html,
      is_correct:   correct ? 1 : 0,
      answer_order: i + 1,
      belongs_question_type: body.question_type
    });
  });

  if (!body.answers.length) 
    return Swal.fire('Warning','Add at least one answer','warning');
  if (!atLeastOneCorrect) 
    return Swal.fire('Warning','Mark one answer as correct','warning');

  const $btn = $('#btnSave')
    .prop('disabled', true)
    .html('<span class="spinner-border spinner-border-sm me-2"></span>Saving…');

  const url    = editingId ? `/api/questions/edit/${editingId}` : '/api/questions/add';
  const method = editingId ? 'PUT' : 'POST';

  const response = await fetch(url, {
    method,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify(body)
  });

  // redirect on unauthorized
  if (response.status === 401 || response.status === 403) {
    return window.location.href = '/unauthorized';
  }

  const j = await response.json();
  $btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Save Question');

  if (!j.success) {
    return Swal.fire('Error', j.message, 'error');
  }

  Swal.fire({
    icon: 'success',
    title: 'Saved!',
    showConfirmButton: false,
    timer: 1200
  }).then(async () => {
    await loadList();
    resetForm();
  });
});


// Fix Summernote dialogs being behind elements
$(document).on('summernote.init', function() {
    $.summernote.options.modal = $.extend($.summernote.options.modal, {
        container: 'body', // Append modals to body
        backdrop: true
    });
});

/* ---------------- init ---------------- */
loadList(); resetForm();
</script>
</body>
</html>
