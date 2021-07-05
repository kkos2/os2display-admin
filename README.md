Os2Display admin for Københavns Kommune
===

# Bundles
## kkos2-display-bundle
This bundle contains slides specific to Københavns Kommune. [More documentation](src/kkos2-display-bundle/README.md) can be found there.

## Tagging for a release
Each pushed commit triggers a google cloud build (see the [cloudbuild](./cloudbuild)) directory which builds a set of container images, pushes them to the shared container registry, and tags the commit with the build number for later deployment. A build can subsequently be deployed via the [os2display-k8s-environments](https://github.com/kkos2/os2display-k8s-environments) repository.

## Visual regression test with backstop.js
For now, this will need to be run locally on your machine. It will be moved to a container eventually.
In the root of this checkout run: `yarn install` to install the test-runner.

Until we have a way to fetch data for the dev environments, you will have to setup slides, channels, and screen yourself for testing. In `backstop.json` you will need to edit the url to the tests too. Make sure you use the "offentligt tilgængelig" url so no interaction is required for the screen.

To test, run `yarn backstop test`. It will fail the first time because there is no reference. Just run `yarn backstop approve` and then run the test again.
