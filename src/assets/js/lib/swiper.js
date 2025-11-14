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
    const swiper = new Swiper(container, {
      direction: 'horizontal',
      loop: true,
      slidesPerView: 5,
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
          slidesPerView: 3,
          spaceBetween: 10,
        },
        640: { // from 640px width
          slidesPerView: 5,
          spaceBetween: 20,
        }
      },
    });

    swiper.update();
  });
});;