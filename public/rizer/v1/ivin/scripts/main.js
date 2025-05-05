import Swiper from 'https://unpkg.com/swiper/swiper-bundle.esm.browser.min.js'

const swiperChooses = new Swiper('#swiperChooses', {

	slidesPerView: 3,
	centeredSlides: true,
	spaceBetween: 30,
	loop: true,
	autoplay: {
	  delay: 0,
	  disableOnInteraction: false,
	},
	breakpoints: {
		"@0.00": {
		  slidesPerView: 1,
		  spaceBetween: 10,
		},
		"@0.75": {
		  slidesPerView: 2,
		  spaceBetween: 20,
		},
		"@1.00": {
		  slidesPerView: 3,
		  spaceBetween: 40,
		},
		"@1.50": {
		  slidesPerView: 3,
		  spaceBetween: 30,
		},
	},
	parallax: true,
	grabCursor: true,
	speed: 3200

})

const swiperIntegrations = new Swiper('#swiperIntegrations', {

	slidesPerView: 6,
	centeredSlides: true,
	spaceBetween: 30,
	loop: true,
	autoplay: {
	  delay: 0,
	  disableOnInteraction: false,
	},
	breakpoints: {
		"@0.00": {
		  slidesPerView: 2,
		  spaceBetween: 10,
		},
		"@0.75": {
		  slidesPerView: 2,
		  spaceBetween: 20,
		},
		"@1.00": {
		  slidesPerView: 3,
		  spaceBetween: 40,
		},
		"@1.50": {
		  slidesPerView: 6,
		  spaceBetween: 30,
		},
	},
	parallax: true,
	grabCursor: true,
	speed: 3200

})

const swiperPossibilitys = new Swiper('#swiperPossibilitys', {

	slidesPerView: 3,
	centeredSlides: true,
	spaceBetween: 30,
	loop: true,
	autoplay: {
	  delay: 0,
	  disableOnInteraction: false,
	},
	breakpoints: {
		"@0.00": {
		  slidesPerView: 1,
		  spaceBetween: 10,
		},
		"@0.75": {
		  slidesPerView: 2,
		  spaceBetween: 20,
		},
		"@1.00": {
		  slidesPerView: 3,
		  spaceBetween: 40,
		},
		"@1.50": {
		  slidesPerView: 3,
		  spaceBetween: 30,
		},
	},
	parallax: true,
	grabCursor: true,
	speed: 3200

})

const swiperVideos = new Swiper('#swiperVideos', {

	slidesPerView: 3,
	centeredSlides: true,
	spaceBetween: 50,
	loop: true,
	autoplay: {
	  delay: 0,
	  disableOnInteraction: false,
	},
	breakpoints: {
		"@0.00": {
		  slidesPerView: 1,
		  spaceBetween: 10,
		},
		"@0.75": {
		  slidesPerView: 2,
		  spaceBetween: 20,
		},
		"@1.00": {
		  slidesPerView: 3,
		  spaceBetween: 40,
		},
		"@1.50": {
		  slidesPerView: 3,
		  spaceBetween: 50,
		},
	},
	parallax: true,
	grabCursor: true,
	speed: 3200

})