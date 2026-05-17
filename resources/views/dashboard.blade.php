<x-layouts::app :title="__('Dashboard')">



     <div class="">

        @auth
            {{-- AUTHENTICATED USER: Show specific/personalized merch --}}
            <h1 class="text-md uppercase italic">Welcome Back, 
               <span class="font-black"> 
                {{ auth()->user()->name ?? 'Guest'  }}
               </span>
            </h1>
            <livewire:platform.home />
        @endauth

        @guest
            {{-- GUEST USER: Show random discovery content --}}
            <h1 class="text-xl font-black italic uppercase italic">Discover the Studio</h1>
            <!--livewire:platform.discovery-feed :limit="10" /-->
            
            <div class="hidden mt-8 p-3 border-2 border-dashed border-black">
                <p class="uppercase text-[10px] font-black tracking-widest">
                Login to save your favorites to the Matrix.
                </p>
            </div>
             <livewire:platform.home />
        @endguest
     </div>





  
</x-layouts::app>
