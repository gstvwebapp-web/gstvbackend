<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>RSS Feed - GSTV</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <link href="{{ URL::asset('public/assets/img/favicon.png') }}" rel="icon">
  <link href="{{ URL::asset('public/assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">
  <style>
    h1 { text-align:center; font-family: 'Open Sans', sans-serif; }
    ul { list-style-type: none; font-family: 'Open Sans', sans-serif;
    margin: 0 auto !important;
    padding: 0px 20px;
    max-width: 940px;
    }
    ul li span:first-child {
     	width: 30%;
    }
    ul li span {
    	width: 70%;
    	word-break: break-all;
    	padding: 0px 5px;
	}
   	ul li {
      display: flex;
      justify-content: space-between;
      text-align: start;
      padding:13px 5px;
	}
    li:first-child {
      background-color: #2b2b2b !important;
      color: #fff;
      font-weight: bold;
      font-size: 20px; 
    }

    li:nth-child(2n) {
        background-color: #eaeaea;
    }

    li:nth-child(2n+1) {
        background-color: #ffffff; 
    }
    ul li span:first-child a {
        color: #000;
    }
    ul li span a { text-decoration: none; color:#3564c9; }
    ul li span a:hover { text-decoration: underline;  }
  </style>
</head>

<body>
  <div>
    <h1>RSS Feed</h1>
    <ul>
      <li><span>Title</span><span>RSS Path</span></li>
      @foreach ($data['newcategory'] as $category)
      <li><span><a href="/category/{{ $category->slug }}">{{ $category->title }}</a></span><span><a href="{{ url($category->slug.'/feed') }}">{{ url($category->slug.'/feed') }}</a></span></li>
      @endforeach
    </ul>
  </div>

</body>
</html>