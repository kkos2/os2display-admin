// Register the function, if it does not already exist.
function getRandomInt(min, max) {
  min = Math.ceil(min);
  max = Math.floor(max);
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

if (!window.slideFunctions["kk-brugbyen"]) {
  window.slideFunctions["kk-brugbyen"] = {
    /**
     * Setup the slide for rendering.
     * @param scope
     *   The slide scope.
     */
    setup: function setupKkEventPlakatSlide(scope) {
      var slide = scope.ikSlide;
      var subslides = [];
      var num_subslides = 0;
      if (slide.external_data && slide.external_data.sis_data_slides) {
        subslides = slide.external_data.sis_data_slides;
        num_subslides = slide.external_data.sis_data_num_slides;
      }
      var slide_duration = slide.options.sis_subslide_duration
        ? slide.options.sis_subslide_duration
        : 10;
      window.slidesInSlides.setup(
        scope,
        subslides,
        num_subslides,
        slide_duration
      );

      scope.ikSlide.kffLogo =
        slide.server_path +
        "/bundles/kkos2displayintegration/assets/img/kbh-logo.png";
      scope.theStyle = {
        bgcolor: slide.options.bgcolor,
      };
      scope.logoVersion = getRandomInt(1, 3);

      scope.ratio = window.kkSlideRatio.getRatio();
    },

    /**
     * Run the slide.
     *
     * @param slide
     *   The slide.
     * @param region
     *   The region to call when the slide has been executed.
     */
    run: function (slide, region) {
      window.slidesInSlides.run(slide, region);
    },
  };
}
