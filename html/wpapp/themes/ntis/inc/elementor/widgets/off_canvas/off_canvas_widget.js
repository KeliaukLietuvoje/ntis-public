jQuery(window).on('elementor/frontend/init', () => {
  class OffcanvasMenu extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
      return {
        selectors: {
          button: '.offcanvas-open',
          content: '.offcanvas-content',
          overlay: '.offcanvas-overlay',
          close: '.offcanvas-close'
        }
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
      document.body.classList.add('offcanvas-open');
    }
      closeMenu() {
        document.body.classList.remove('offcanvas-open');
      }
    }
  
    elementorFrontend.hooks.addAction('frontend/element_ready/ntis-off-canvas.default', ($element) => {
      elementorFrontend.elementsHandler.addHandler(OffcanvasMenu, {
        $element
      });
    });
  });