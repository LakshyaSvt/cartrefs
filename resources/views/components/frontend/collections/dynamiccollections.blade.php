@if (isset($dynamiccollections))

    @if (count($dynamiccollections) > 0)

        @foreach ($dynamiccollections as $dynamiccollection)

        <section id="dynamiccollections{{ $dynamiccollection->id }}">
            <div class="containers pt-10 pb-6 pr-4 pl-4" @if(!empty($dynamiccollection->background_color)) style="background: {{ $dynamiccollection->background_color }};" @endif>
                
                <h2 class="title title-center mb-4">
                    {{ $dynamiccollection->group_name }}
                </h2>
    
                
                {{-- <div class="banner-desc-container">
                    <p>{{ setting('3-column-collection.description') }}</p>
                </div> --}}

                @php
                    // $take = floor($dynamiccollection->collections->count() / $dynamiccollection->desktop_columns) * $dynamiccollection->desktop_columns;
                    $collections = $dynamiccollection->collections()->get();
                @endphp

                <style>

                    @media (min-width: 901px)
                    {
                        .dynamiccollections{{ $dynamiccollection->id }}{
                            display: grid;
                            grid-template-columns: repeat({{ $dynamiccollection->desktop_columns }}, 1fr);
                            /* grid-template-columns: repeat(8, 1fr); */
                            grid-gap: {{ $dynamiccollection->desktop_gap }}em;
                        }

                        #dynamiccollections{{ $dynamiccollection->id }} h2.title{
                            font-size: 2.3em;
                            font-family: 'Poppins', sans-serif;
                            margin-bottom: 1em !important;
                            font-weight: 500;
                        }

                    }

                    @media (max-width: 900px)
                    {
                        .dynamiccollections{{ $dynamiccollection->id }}{
                            display: grid;
                            grid-template-columns: repeat({{ $dynamiccollection->tablet_columns }}, 1fr);
                            grid-gap: {{ $dynamiccollection->tablet_gap }}em;
                        }

                        #dynamiccollections{{ $dynamiccollection->id }} h2.title{
                            font-size: 2.3em;
                            font-family: 'Poppins', sans-serif;
                            margin-bottom: 1em !important;
                            font-weight: 500;
                        }

                        
                    }

                    @media (max-width: 600px)
                    {
                        .dynamiccollections{{ $dynamiccollection->id }}{
                            display: grid;
                            grid-template-columns: repeat({{ $dynamiccollection->mobile_columns }}, 1fr);
                            grid-gap: {{ $dynamiccollection->mobile_gap }}em;
                        }

                        #dynamiccollections{{ $dynamiccollection->id }} h2.title{
                            font-size: 1.5em;
                            font-family: 'Poppins', sans-serif;
                            margin-bottom: 1em !important;
                            font-weight: 600;
                        }
                    }

                </style>



                {{-- Visiblity --}}
                @if ($dynamiccollection->desktop_visiblity == 0)
                    
                    <style>
                        @media (min-width: 901px)
                        {
                            #dynamiccollections{{ $dynamiccollection->id }} {
                                display: none;
                            }
                        }
                    </style>

                @endif


                @if ($dynamiccollection->tablet_visiblity == 0)
                    
                    <style>
                        @media (min-width: 601px) AND (max-width: 900px)
                        {
                            #dynamiccollections{{ $dynamiccollection->id }} {
                                display: none;
                            }
                        }
                    </style>

                @endif

                @if ($dynamiccollection->mobile_visiblity == 0)
                    
                    <style>
                        @media (max-width: 600px)
                        {
                            #dynamiccollections{{ $dynamiccollection->id }} {
                                display: none;
                            }
                        }
                    </style>

                @endif


                {{-- Caousel --}}

                @if ($dynamiccollection->desktop_carousel == 1)
                    <style>
                        #dynamiccollections{{ $dynamiccollection->id }} .dynamiccollections{{ $dynamiccollection->id }}.carousel {
                            /* display: block !important; */
                        }
                        #dynamiccollections{{ $dynamiccollection->id }} .dynamiccollections{{ $dynamiccollection->id }}.nocarousel {
                            display: none !important;
                        }
                    </style>
                @elseif($dynamiccollection->tablet_carousel == 1)
                    <style>
                        #dynamiccollections{{ $dynamiccollection->id }} .dynamiccollections{{ $dynamiccollection->id }}.carousel {
                            /* display: block !important; */
                        }
                        #dynamiccollections{{ $dynamiccollection->id }} .dynamiccollections{{ $dynamiccollection->id }}.nocarousel {
                            display: none !important;
                        }
                    </style>
                @elseif($dynamiccollection->mobile_carousel == 1)
                    <style>
                        #dynamiccollections{{ $dynamiccollection->id }} .dynamiccollections{{ $dynamiccollection->id }}.carousel {
                            /* display: block !important; */
                        }
                        #dynamiccollections{{ $dynamiccollection->id }} .dynamiccollections{{ $dynamiccollection->id }}.nocarousel {
                            display: none !important;
                        }
                    </style>
                @else
                    
                    <style>
                        #dynamiccollections{{ $dynamiccollection->id }} .dynamiccollections{{ $dynamiccollection->id }}.carousel {
                            display: none !important;
                        }
                        #dynamiccollections{{ $dynamiccollection->id }} .dynamiccollections{{ $dynamiccollection->id }}.nocarousel {
                            /* display: block !important; */
                        }
                    </style>

                @endif


                

                <div class="dynamiccollections{{ $dynamiccollection->id }} owl-carousel owl-theme owl-nav-bg owl-nav-arrow carousel" data-owl-options="{
                    'items': {{ $dynamiccollection->desktop_columns }},
                    'autoplay': 10,
                    'slideSpeed': 300,
                    'loop': true,
                    'nav': true,
                    'dots': true,
                    'animateIn': 'fadeIn',
                    'animateOut': 'fadeOut'
                    }">
                    @foreach ($collections as $collection)
                    <div class="dynamiccollection">
                        <a href="{{ $collection->url }}">
                            <div class="image">
                                <figure>
                                    <img src="{{ Voyager::image($collection->image) }}" alt="{{ $dynamiccollection->group_name }}" style="background-color: #ccc;">
                                </figure>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>

                <div class="dynamiccollections{{ $dynamiccollection->id }} nocarousel">
                    @foreach ($collections as $collection)
                    <div class="dynamiccollection">
                        <a href="{{ $collection->url }}">
                            <div class="image">
                                <figure>
                                    <img src="{{ Voyager::image($collection->image) }}" alt="{{ $dynamiccollection->group_name }}" style="background-color: #ccc;">
                                </figure>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
                
            </div>
        </section>

        @endforeach
    @endif
        
@endif