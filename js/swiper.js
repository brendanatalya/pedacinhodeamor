      var swiper = new Swiper('.swiper', {
        loop: true,
        grabCursor: true,
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },

        pagination: {
          el: '.swiper-pagination',
          clickable: true,
        },

        breakpoints: {

        640: {
        slidesPerView: 2,
        spaceBetween: 20
        },

        768: {
        slidesPerView: 3,
        spaceBetween: 18
        },

        1188: {
        slidesPerView: 4,
        spaceBetween: 30
        }
    }
      }); 

      /*carrossel de avalizações*/
      
        var swiperstar = new Swiper('#avaliacao', {
        loop: true,
        grabCursor: true,
        navigation: {
          nextEl: '.swiper-button-nextstar',
          prevEl: '.swiper-button-prevstar',
        },

        pagination: {
          el: '.swiper-paginationstar',
          clickable: true,
        },

        breakpoints: {

        640: {
        slidesPerView: 2,
        spaceBetween: 20
        },

        768: {
        slidesPerView: 3,
        spaceBetween: 18
        },

        1188: {
        slidesPerView: 4,
        spaceBetween: 30
        }
    }
      }); 