// Register the function, if it does not already exist.
if (!window.slideFunctions["bookbyen"]) {
  window.slideFunctions["bookbyen"] = {
    /**
     * Setup the slide for rendering.
     * @param scope
     *   The slide scope.
     */
    setup: scope => {
      const slide = scope.ikSlide;
      let subslides = [];
      let num_subslides = 0;
      if (slide.external_data && slide.external_data.sis_data_slides) {
        subslides = slide.external_data.sis_data_slides;
        num_subslides = slide.external_data.sis_data_num_slides;
      }
      const slide_duration = slide.options.sis_subslide_duration
        ? slide.options.sis_subslide_duration
        : 10;
      window.slidesInSlides.setup(
        scope,
        subslides,
        num_subslides,
        slide_duration
      );

      scope.useField = slide.options.useFields;

      scope.ikSlide.kffLogo = slide.server_path + "/bundles/kkos2displayintegration/assets/img/kbh-logo.png";

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
    run: (slide, region) => {
      window.slidesInSlides.run(slide, region);
    }
  };
}
