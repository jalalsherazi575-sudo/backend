@extends('front.layouts.contactus')
@section('scripts')
    <script src="/assets/front/js/contactus/validation.js"></script>
    <script src="/assets/front/js/ddaccordion.js"></script>
  <script>
ddaccordion.init({
  headerclass: "submenuheader", 
  contentclass: "faqsubmenu ", 
  revealtype: "click", 
  mouseoverdelay: 200, 
  collapseprev: true, 
  defaultexpanded: [0],
  onemustopen: false, 
  animatedefault: false,
  persiststate: false, 
  toggleclass: ["", "openHeader"],  
  animatespeed: "fast", 
  oninit:function(headers, expandedindices){},
  onopenclose:function(header, index, state, isuseractivated){}
})
</script>    
@stop

@section('content')
    <section class="section section-hero-area webpage">
        <div class="text-sm-center">
             <div class="row">
                <div class="col-sm-12">	
                    <div class="card center">
                      <div class="card-body">
                             
                          <div class="faqwrap">       
                               <div class="container"> 
                                <!-- <h2>Frequently asked questions</h2> -->
                                    @if ($faqData)
                                       @foreach($faqData as $rows)
                                    <div class="row-wrap">
                                      <a class="menuitem submenuheader" href="#">{{$rows->name}}</a>    
                                      <div class="faqsubmenu"> {!! $rows->answer !!}</div>      
                                    </div>
                                       @endforeach
                                    @else
                                    <div class="row-wrap"><a class="menuitem submenuheader" href="#">Currently No FAQ's Added.</a></div>
                                    @endif

                                    
                                </div>
                          </div>

            	
                      </div>
                    </div>
                </div>
            </div>
        </div>        
    </section>
@endsection