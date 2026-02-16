// Import Swiper bundle with all modules installed
import Swiper from 'swiper/bundle';
import 'swiper/css/bundle';
function setSwiperContainerHeight(swiperEl) {
  if (!swiperEl) return;

  let tallest = 0;

  swiperEl.querySelectorAll('.swiper-slide').forEach(slide => {
    // Temporarily remove collapse to measure natural height
    const prevMaxHeight = slide.style.maxHeight;
    slide.style.maxHeight = 'none';
    tallest = Math.max(tallest, slide.scrollHeight);
    slide.style.maxHeight = prevMaxHeight; // restore
  });

const container = document.querySelector('.swiper.slide-carousel');
container.style.height = Math.ceil(tallest * 1.4) + 'px';
}

window.addEventListener('load', () => {
  const swiperContainers = document.querySelectorAll('.swiper.slide-carousel');

  swiperContainers.forEach(container => {
    const swiper = new Swiper(container, {
      direction: 'horizontal',
      loop: true,
      slidesPerView: 2,
      spaceBetween: 20,
      observer: true,
      observeParents: true,
      navigation: {
        nextEl: container.querySelector('.swiper-button-next'),
        prevEl: container.querySelector('.swiper-button-prev'),
      },
      autoplay: {
        delay: 6400,
        disableOnInteraction: false,
      },
        speed: 1600,
        breakpoints: {
          0: { // from 0px width
            slidesPerView: 1,
            spaceBetween: 10,
            },
        
          640: { // from 768px width
            slidesPerView: 2,
            spaceBetween: 20,
          }
        },
      on: {
        init: () => setSwiperContainerHeight(container),
      },
    });

    swiper.update();

   
  });

 const resourcesContainers = document.querySelectorAll('.swiper.resourcescarousel');

 resourcesContainers.forEach(container => {
   // 1. Count the actual slides inside this specific container
   const slideCount = container.querySelectorAll('.swiper-slide').length;

   // 2. Define your threshold (e.g., loop only if slides > 5)
   const shouldLoop = slideCount > 5;

   const swiper = new Swiper(container, {
     direction: 'horizontal',
     // 3. Apply the dynamic boolean
     loop: shouldLoop,
     slidesPerView: 5,
     spaceBetween: 30,
     observer: true,
     observeParents: true,
     navigation: {
       nextEl: container.querySelector('.swiper-button-next'),
       prevEl: container.querySelector('.swiper-button-prev'),
     },
     autoplay: {
       delay: 6400,
       disableOnInteraction: false,
     },
     speed: 1600,
     breakpoints: {
       0: {
         slidesPerView: 3,
         spaceBetween: 10,
         // Optional: Update loop per breakpoint if needed
       },
       640: {
         slidesPerView: 4,
         spaceBetween: 30,
       }
     },
   });

   swiper.update();
 });
});