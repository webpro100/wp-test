import '../scss/main.scss';

import { gsap } from 'gsap';
import ScrollTrigger from 'gsap/ScrollTrigger';
import SplitText from 'gsap/SplitText';
import Lenis from 'lenis';
import $ from "jquery"

import Swiper from 'swiper/bundle';
import "swiper/css/bundle"



gsap.registerPlugin(ScrollTrigger, SplitText);


document.addEventListener('DOMContentLoaded', async () => {




const swiper = new Swiper('.swiper_1', {
  // Optional parameters
  loop: true,

  keyboard:{
    enabled: true,
    onlyInViewport: true
  },

  // If we need pagination
  pagination: {
    el: '.pagination_1',
  },

  // Navigation arrows
  navigation: {
    nextEl: '.next_1',
    prevEl: '.prev_1',
  },

  // And if we need scrollbar
  scrollbar: {
    el: '.scrollbar_1',
  },
});



const swiper2 = new Swiper('.swiper_2', {
  // Optional parameters
  loop: true,

  keyboard:{
    enabled: true,
    onlyInViewport: true
  },

  // If we need pagination
  pagination: {
    el: '.pagination_2',
  },

  // Navigation arrows
  navigation: {
    nextEl: '.next_2',
    prevEl: '.prev_2',
  },

  // And if we need scrollbar
  scrollbar: {
    el: '.scrollbar_2',
  },
});

 


// split elements with the class "split" into words and characters
let split = SplitText.create("h1", { type: "chars" });

// now animate the characters in a staggered fashion
gsap.from(split.chars, {
  duration: 1,      // animate from 100px below
  autoAlpha: 0, // fade in from opacity: 0 and visibility: hidden
  stagger: 0.05, // 0.05 seconds between each.
  onComplete(){
        gsap.to(".btn", {
            opacity: 1,
            duration:1,
            ease: "power2.out"
            }, "+=0.2");
  }
});




gsap.to("header", {
    backgroundColor: "#000",
    ease: "power2.in",
    scrollTrigger: {
        start: 10,
        end: 50,
        scrub: true

    },

});

gsap.to("#hero", {
    backgroundPosition: "0 100%",
    ease: "power2.in",
    scrollTrigger: {
        trigger: "#hero",
        start: "top top",
        end: "bottom top",
        scrub: true
    }

});







  // Lenis
  const lenis = new Lenis();
  function raf(t){ lenis.raf(t); requestAnimationFrame(raf); }
  requestAnimationFrame(raf);
  lenis.on('scroll', ScrollTrigger.update);
  
});


function checkActive() {
	$(".home section").each(function(){
		if((($(window).scrollTop()+($(window).height()/2) ) > $(this).offset().top) && (($(window).scrollTop()+($(window).height()*0.5) ) < $(this).offset().top+$(this).outerHeight(true))) {
			if(!$(this).hasClass("active")) {
				$(".home section").removeClass("active");
				$(this).addClass("active");

                console.log($(this).attr("id"));
        
                let tag = $(this).attr("id");

                $("#menu-main-menu a").removeClass("active");
                $("#menu-main-menu a[href='#"+tag+"']").addClass("active");


				// $("#line_nav>div>span").eq($(this).index()).addClass("active");
			}
		}
	});
}	

if($("body").hasClass("home")) {
	
	
	$(window).scroll(function(){
		
		checkActive();
		
	});		
}

$(window).load(function(){
	checkActive();
});












// gsap.to("header", {
//     opacity: "0",
//     top: "-100vh",
//     ease: "power2.in",
//     scrollTrigger: {
//         trigger: ".ws-wrapper",
//         start: "top top",
//         end: "bottom bottom+=75%",
//         scrub: true,
//         onLeave: () => gsap.set("header", {
//             zIndex: -1
//         }),
//         onEnterBack: () => gsap.set("header", {
//             zIndex: 2
//         })
//     },

// })


// gsap.to("header h1", {
//     opacity: "0",
//     y: "-30vh",
//     filter: "blur(100px)",
//     scale: 20,
//     ease: "power2.in",
//     scrollTrigger: {
//         trigger: ".ws-wrapper",
//         start: "top top",
//         end: "bottom bottom+=50%",
//         scrub: true,
//     },

// });

// gsap.to(".scroll-text", {
//     opacity: "0",
//     y: "-20vh",
//     ease: "power2.in",
//     scrollTrigger: {
//         trigger: ".ws-wrapper",
//         start: "top top",
//         end: "bottom bottom+=50%",
//         scrub: true,
//     },
// });

// gsap.to(".ws-map", {
//     left: aside.offsetWidth,
//     ease: "power2.out",
//     scrollTrigger: {
//         trigger: ".ws-wrapper",
//         start: "top top",
//         end: "bottom bottom+=50%",
//         scrub: true
//     }
// });



// import Swiper from 'swiper'
// import 'swiper/css/bundle';
// const swiper = new Swiper('.swiper', {
//   // Optional parameters
//   direction: 'horizontal',
//   loop: true,

//   // If we need pagination
//   pagination: {
//     el: '.swiper-pagination',
//   },

//   // Navigation arrows
//   navigation: {
//     nextEl: '.swiper-button-next',
//     prevEl: '.swiper-button-prev',
//   },

//   // And if we need scrollbar
//   scrollbar: {
//     el: '.swiper-scrollbar',
//   },
// });