jQuery(window).on('elementor/frontend/init', () => {
  class Ntis_Off_Canvas extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
      return {
        selectors: {
          button: '.ntis-off-canvas-open',
          content: '.ntis-off-canvas-content',
          overlay: '.ntis-off-canvas-overlay',
          close: '.ntis-off-canvas-close'
        },
      };
    }

    getDefaultElements() {
      const selectors = this.getSettings('selectors');
      return {
        $button: this.$element.find(selectors.button),
        $content: this.$element.find(selectors.content),
        $overlay: this.$element.find(selectors.overlay),
        $close: this.$element.find(selectors.close)
      };
    }

    bindEvents() {
      this.elements.$button.on('click', () => this.openMenu());
      this.elements.$overlay.on('click', () => this.closeMenu());
      this.elements.$close.on('click', () => this.closeMenu());
    }

    openMenu() {
      document.body.classList.add('ntis-off-canvas-open');
    }
    closeMenu() {
      document.body.classList.remove('ntis-off-canvas-open');
    }
  }

  elementorFrontend.hooks.addAction('frontend/element_ready/ntis_off_canvas.default', ($element) => {
    elementorFrontend.elementsHandler.addHandler(Ntis_Off_Canvas, {
      $element
    });
    document.body.classList.add('ntis-off-canvas-enabled');
  });
});