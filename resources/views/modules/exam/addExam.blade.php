<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Exam</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font-Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <!-- Summernote -->
  <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Page CSS -->
  <link rel="stylesheet" href="{{ asset('css/pages/exam/addExam.css') }}"/>

  <!-- jQuery + Summernote JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
</head>
<body class="bg-light">
  <div class="container-fluid">
    <div class="row">
      <main class="col-md-12 ms-sm-auto px-md-4 py-4">
        <div class="rounded-3 p-4">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><a href="#"><i class="fas fa-file-alt me-1"></i>Exam</a></li>
              <li class="breadcrumb-item active">Add</li>
            </ol>
            <div class="d-none d-sm-flex align-items-center gap-2 px-3 py-2 bg-white rounded-3 text-muted">
              <i class="far fa-calendar-alt"></i>
              <span id="currentDate"></span>
            </div>
          </div><hr>
        </div>

        {{-- ========== FORM ========== --}}
        <form id="examForm" enctype="multipart/form-data">
          <div class="row g-4">
            {{-- ---------- Left column ---------- --}}
            <div class="col-lg-8">
              <div class="glass-effect rounded-3 shadow-sm p-4 h-100">
                <h5 class="mb-4 d-flex align-items-center gap-2">
                  <i class="fas fa-info-circle text-primary"></i> Exam Information
                </h5>

                {{-- name --}}
                <div class="mb-4">
                  <label class="form-label fw-medium" for="examName">Exam Name</label>
                  <input class="form-control border-2 py-2" id="examName" name="examName" required>
                </div>

                {{-- description --}}
                <div class="mb-4">
                  <label class="form-label fw-medium">Description</label>
                  <textarea id="examDescription" name="examDescription" class="form-control"></textarea>
                </div>

                {{-- instructions --}}
                <div>
                  <label class="form-label fw-medium">Instructions</label>
                  <textarea id="Instructions" name="Instructions" class="form-control"></textarea>
                </div>
              </div>
            </div>

            {{-- ---------- Right column ---------- --}}
            <div class="col-lg-4">
              <div class="glass-effect rounded-3 shadow-sm p-4 h-100">
                <h5 class="mb-4 d-flex align-items-center gap-2">
                  <i class="fas fa-cog text-primary"></i> Exam Settings
                </h5>

                {{-- image --}}
                <div class="mb-4">
                  <label class="form-label fw-medium">Exam Image</label>
                  <div class="border-2 border-dashed rounded-3 p-4 text-center cursor-pointer hover-lift"
                       onclick="$('#examImg').click()">
                    <div id="imagePreviewContent" class="text-muted">
                      <i class="fas fa-cloud-arrow-up fa-2x mb-2 text-primary"></i>
                      <p class="mb-0 small">Click to upload image</p>
                      <p class="small text-muted">PNG/JPG ≤ 2&nbsp;MB</p>
                    </div>
                  </div>
                  <input hidden id="examImg" name="examImg" type="file" accept="image/*">
                </div>

                {{-- publish --}}
                <div class="form-check form-switch mb-4">
                  <input class="form-check-input" id="isPublic" name="is_public" type="checkbox" value="yes">
                  <label class="form-check-label fw-medium" for="isPublic">Publish Exam</label>
                </div>

                {{-- pricing --}}
                <div class="mb-4">
                  <label class="form-label fw-medium mb-2">Pricing Model</label>
                  <div class="btn-group w-100" role="group">
                    <input class="btn-check" type="radio" name="pricing_model" id="freeOption" value="free" checked>
                    <label class="btn btn-outline-primary" for="freeOption">Free</label>

                    <input class="btn-check" type="radio" name="pricing_model" id="paidOption" value="paid">
                    <label class="btn btn-outline-primary" for="paidOption">Paid</label>
                  </div>
                </div>

                {{-- paid inputs --}}
                <div id="paidPricingInputs" class="mb-4 d-none">
                  <div class="mb-3">
                    <label class="form-label fw-medium" for="regularPrice">Regular Price</label>
                    <div class="input-group">
                      <span class="input-group-text">₹</span>
                      <input class="form-control" id="regularPrice" name="regular_price" type="number" min="0" step="0.01">
                    </div>
                  </div>
                  <div>
                    <label class="form-label fw-medium" for="salePrice">Sale Price</label>
                    <div class="input-group">
                      <span class="input-group-text">₹</span>
                      <input class="form-control" id="salePrice" name="sale_price" type="number" min="0" step="0.01">
                    </div>
                  </div>
                </div>

                {{-- result release --}}
                <div class="mb-3">
                  <label class="form-label fw-medium" for="resultSetUpType">Result Release</label>
                  <select class="form-select" id="resultSetUpType" name="result_set_up_type">
                    <option value="Immediately">Immediately</option>
                    <option value="Schedule">Schedule</option>
                  </select>
                </div>
                <div id="releaseDateContainer" class="mb-4 d-none">
                  <label class="form-label fw-medium" for="resultReleaseDate">Release Date</label>
                  <input class="form-control" id="resultReleaseDate" name="result_release_date" type="date">
                </div>

                {{-- NEW: time / attempts / questions --}}
                <h6 class="fw-semibold mt-4 mb-3"><i class="fas fa-stopwatch me-2 text-primary"></i>Exam Parameters</h6>
                <div class="mb-3">
                  <label class="form-label fw-medium" for="totalTime">Total Time (minutes)</label>
                  <input class="form-control" id="totalTime" name="totalTime" type="number" min="1" required>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-medium" for="totalAttempts">Attempts Allowed</label>
                  <input class="form-control" id="totalAttempts" name="total_attempts" type="number" min="1" value="1">
                </div>

                {{-- dynamic course select --}}
                <div class="mb-3">
                  <label class="form-label fw-medium" for="associatedCourse">Associated Course</label>
                  <select class="form-select" id="associatedCourse" name="associated_course" required>
                    <option value="" selected disabled>Select a course</option>
                  </select>
                </div>

                {{-- department --}}
                <div>
                  <label class="form-label fw-medium" for="associatedDept">Associated Department</label>
                  <input class="form-control" id="associatedDept" name="associated_department">
                </div>
              </div>
            </div>
          </div>

          {{-- submit --}}
          <div class="text-center mt-4">
            <button id="submitBtn" class="btn btn-primary px-4 py-2 gradient-btn hover-lift">
              <i class="fas fa-plus-circle me-2"></i>Create Exam
            </button>
          </div>
        </form>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const token = localStorage.getItem('token') || sessionStorage.getItem('token');
    const apiHeaders = {
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`
    };

    $(function () {
      $('#currentDate').text(new Date().toLocaleDateString('en-US',
        {weekday:'short',month:'short',day:'numeric'}));

      $('#examDescription,#Instructions').summernote({
        height:150,
        placeholder:'Enter details…',
        toolbar:[
          ['style',['bold','italic','underline','clear']],
          ['para',['ul','ol','paragraph']],
          ['insert',['link']],
          ['view',['fullscreen','codeview']]
        ]
      });

      $('input[name="pricing_model"]').on('change',
        () => $('#paidPricingInputs').toggleClass('d-none', !$('#paidOption').is(':checked')));
      $('#resultSetUpType').on('change',
        () => $('#releaseDateContainer').toggleClass('d-none', $('#resultSetUpType').val()!=='Schedule'));

      $('#examImg').on('change', function(){
        const f=this.files[0];
        if(!f) return;
        if(f.size>2*1024*1024){
          Swal.fire('Error','Image size must be ≤ 2&nbsp;MB','error'); this.value=''; return;
        }
        $('#imagePreviewContent').html(`<img src="${URL.createObjectURL(f)}" class="img-fluid rounded-3">`);
      });

      // load courses for the select
      async function loadCourseOptions() {
        try {
          const res  = await fetch('/api/courses', { headers: apiHeaders });
          const json = await res.json();
          if (!res.ok || !json.success) throw new Error(json.message || 'Failed to load courses');

          const $select = $('#associatedCourse');
          json.courses.forEach(c => {
            $select.append(
              `<option value="${c.title}">${c.title}</option>`
            );
          });
        } catch (err) {
          console.error('Course load error:', err);
          Swal.fire('Error', 'Could not load courses list', 'error');
        }
      }

      loadCourseOptions();

      $('#examForm').on('submit', async function(e){
        e.preventDefault();
        const $btn=$('#submitBtn').prop('disabled',true)
          .html('<span class="spinner-border spinner-border-sm me-2"></span>Creating…');

        const fd=new FormData(this);
        fd.set('admin_id',1);
        fd.set('examDescription',$('#examDescription').summernote('code'));
        fd.set('Instructions',  $('#Instructions').summernote('code'));
        fd.set('is_public', $('#isPublic').is(':checked') ? 'yes' : 'no');

        try{
          const res=await fetch('/api/exam',{
            method:'POST',
            headers:{
              'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content'),
              'Authorization':`Bearer ${token}`
            },
            body:fd
          });

          const isJson=res.headers.get('content-type')?.includes('application/json');
          const data=isJson ? await res.json() : null;

          if(!res.ok||!data?.success) throw new Error(data?.message||'Server error');

          await Swal.fire({icon:'success',title:'Success',
                           text:'Exam created successfully!',confirmButtonColor:'#4e73df'});
          this.reset();
          $('#examDescription,#Instructions').summernote('code','');
          $('#imagePreviewContent').html(
            '<i class="fas fa-cloud-arrow-up fa-2x mb-2 text-primary"></i>'+
            '<p class="mb-0 small">Click to upload image</p>'+
            '<p class="small text-muted">PNG/JPG ≤ 2&nbsp;MB</p>');
          $('#paidPricingInputs,#releaseDateContainer').addClass('d-none');
          $('#freeOption').prop('checked',true);
        }catch(err){
          Swal.fire({icon:'error',title:'Error',text:err.message,confirmButtonColor:'#4e73df'});
        }finally{
          $btn.prop('disabled',false)
              .html('<i class="fas fa-plus-circle me-2"></i>Create Exam');
        }
      });
    });
  </script>
</body>
</html>
