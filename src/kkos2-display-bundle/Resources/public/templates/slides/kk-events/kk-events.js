// Register the function, if it does not already exist.
if (!window.slideFunctions['kk-events']) {
  window.slideFunctions['kk-events'] = {
    /**
     * Setup the slide for rendering.
     * @param scope
     *   The slide scope.
     */
    setup: function setupKkEventsSlide(scope) {
      var slide = scope.ikSlide;
      var subslides = [];
      var num_subslides = 0;
      if (slide.external_data && slide.external_data.sis_data_slides) {
        subslides = slide.external_data.sis_data_slides;
        num_subslides = slide.external_data.sis_data_num_slides;
      }
      var slide_duration = slide.options.sis_subslide_duration ? slide.options.sis_subslide_duration : 10;

      // Just hardcode path to logo.
      scope.ikSlide.kffLogo = slide.server_path + "/bundles/kkos2displayintegration/assets/img/kbh-logo.png";

      scope.theStyle = {
        bgcolor: slide.options.bgcolor
      };
      scope.itemsPrSlide = slide.external_data.sis_data_items_pr_slide ? slide.external_data.sis_data_items_pr_slide : 1;
      scope.ratio = window.kkSlideRatio.getRatio();
      window.slidesInSlides.setup(scope, subslides, num_subslides, slide_duration);
    },

    /**
     * Run the slide.
     *
     * @param slide
     *   The slide.
     * @param region
     *   The region to call when the slide has been executed.
     */
    run: function runEventsSlide(slide, region) {
      window.slidesInSlides.run(slide, region);
    }
  };
}
