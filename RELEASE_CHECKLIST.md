# Making a release

In order to make a release, each of these steps should be followed:

- Updating the Makefile to change the version number
- Update the `contrib` folders with new `compose.yaml` and `.env` (So that your user can copy-paste them and simply update their `.env`, a-la immich)
- `git tag -a "v"0.1.1"" -m ""0.1.1""`
- `git push --follow-tags`
- **Wait for the GHA to build the image**
- Edit the release on Github to add the changelog
