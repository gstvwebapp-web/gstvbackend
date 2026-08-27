@extends('layouts.app')

@section('content')
@php
$dataarr = array(
     'Header Side' => array('header_top' => 'Header'),
    'Right Side' => array('right_top' => 'Right Side Top', 'right_middle' => 'Right Side Middle', 'right_bottom' => 'Right Side Bottom'),
    'Left Side' => array('left_top' => 'Left Side Top', 'left_middle' => 'Left Side Middle', 'left_left' => 'Left Side Bottom'),
    'Footer' => array('footer_left' => 'Footer'),
    'Category ' => array('cat_stories' => 'after 4 stories'),
    'Webstories ' => array('web_stories' => 'Webstories'),
    'News ' => array('news_above_img' => 'Above Image', 'news_first_paragraph' => 'first Paragraph', 'news_second_paragraph' => 'Second Paragraph', 'news_third_paragraph' => 'Third Paragraph', 'news_end_stories' => 'End of stories')
);
$adsdataarr=json_decode($data['ads']['adsdata']);
@endphp
<main id="main" class="main">

    <div class="pagetitle">
      <h1>Ads</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/ads') }}">Ads</a></li>
          <li class="breadcrumb-item active">Edit</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    
      <section class="section">
      <div class="row">
        
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body pt-4">
              
              <!-- Custom Styled Validation -->
               
              <!-- Custom Styled Validation -->
              <form class="row g-3 needs-validation adspage" method="POST" action="{{ url('/ads/update') }}" enctype="multipart/form-data" novalidate>
                  @csrf
                  <input type="hidden" name="adsid" value="{{ $data['ads']->id }}" />
                  @foreach($dataarr as $datakey => $dataval)
                  <h4>{{ $datakey }}</h4>
                  @foreach($dataval as $index => $val)
                      <div class="col-md-12 form-container">
                          <div class="col-md-10">
                              <div class="form-check form-switch mb-3">
                                  <input class="form-check-input" type="checkbox" id="flexSwitchCheck{{ $index }}" name="enable[{{ $index }}]" onchange="toggleTextarea(this, '{{ $index }}')" {{ isset($adsdataarr->enable->$index) && $adsdataarr->enable->$index === 'on' ? 'checked' : '' }}>
                                  <label class="form-check-label" for="flexSwitchCheck{{ $index }}">{{ $val }}</label>
                              </div>
                              <textarea name="data[{{ $index }}]" class="form-control" id="validationCustom{{ $index }}" rows="5" >{{ isset($adsdataarr->data->$index) ? trim($adsdataarr->data->$index) : old('data.'.$index) }}</textarea>
                              <div class="invalid-feedback">
                                  Please enter the Question.
                              </div>
                              @error('data.'.$index)
                                  <div class="alert alert-danger">{{ $message }}</div>
                              @enderror
                          </div>
                      </div>
                  @endforeach
              @endforeach

                  <div class="col-12">
                      <button class="btn btn-primary" type="submit">Submit</button>
                  </div>
              </form><!-- End Custom Styled Validation -->
            </div>
          </div>

        

        </div>
      </div>
    </section>

     

  </main><!-- End #main -->
  <script>
    function toggleTextarea(checkbox, index) {
        var textarea = document.getElementById('validationCustom' + index);
        // textarea.disabled = !checkbox.checked;
        // if (!checkbox.checked) {
        //     textarea.value = ''; // Clear textarea if checkbox is unchecked
        // }
    }
</script>
@endsection