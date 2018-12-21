<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="keywords" content="مقالات آموزشی الکترونیک,آی سی,ای سی,ماژول,مقاومت,خازن,ترانزیستور,فروش اینترنتی,الکترونیک,آردوینو,رباتیک,میکروکنترلر,قطعات الکترونیک,فروش">
            <meta name="description"
                  content="فروشگاه آنلاین قطعات الکترونیک و مجله آموزشی الکترونیک"/>
        <title>ETRIX - فروشگاه قطعات الکترونیک</title>
        <link href="{{asset('css/app.css')}}" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    </head>
    <body>
     <div id="example">
     </div>

     <style>
     @font-face {
         font-family: myFirstFont;
         src: url(font/3.woff);
     }

     body {
         font-family: myFirstFont;
     }
     </style>
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
         <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
         <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>

     <script src="{{ mix('js/manifest.js') }}"></script>
          <script src="{{ mix('js/vendor.js') }}"></script>
          <script src="{{ mix('js/app.js') }}"></script>
     <script>
     $(document).ready(function () {

         $('.iconbar-container').click(function () {

             $(this).toggleClass('change');

             $(".navbar-sticky-bg").fadeIn('slow',function () {
                 $("body").css({overflow : "hidden"});
                 $('.navbar-sticky').animate({'left' : $(window).outerWidth()-$(".navbar-sticky").outerWidth()},'fast');

             });

         });

         $("body").click(function (e) {
             // console.log(e.target.classList);
             if(e.target.classList.contains('navbar-sticky-bg') || e.target.classList.contains('list-group-item')){
                 // console.log("test navbar");
                 $('.navbar-sticky')
                     .animate({"left": "100%"}, 'fast');

                 $('.navbar-sticky-bg').fadeOut('slow',function () {
                     $("body").css({overflow : "visible"});
                     $('.iconbar-container').toggleClass('change');
                 });
             }
         })

         $(".list-group-item a").click(function (e) {
             $('.navbar-sticky')
                 .animate({"left": "100%"}, 'fast');

             $('.navbar-sticky-bg').fadeOut('slow',function () {
                 $("body").css({overflow : "visible"});
                 $('.iconbar-container').toggleClass('change');
             });
         })

     });
     </script>
     <!-- Global site tag (gtag.js) - Google Analytics -->
     <script async src="https://www.googletagmanager.com/gtag/js?id=UA-131109496-1"></script>
     <script>
       window.dataLayer = window.dataLayer || [];
       function gtag(){dataLayer.push(arguments);}
       gtag('js', new Date());

       gtag('config', 'UA-131109496-1');
     </script>

    </body>
</html>
