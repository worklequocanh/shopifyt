/**
 * Image Compressor
 * Compresses images larger than max size using Canvas API
 */
const ImageCompressor = {
  async compress(file, maxSizeMB = 10, quality = 0.7) {
    if (file.size <= maxSizeMB * 1024 * 1024) {
      return file;
    }

    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.readAsDataURL(file);

      reader.onload = (event) => {
        const img = new Image();
        img.src = event.target.result;

        img.onload = () => {
          const canvas = document.createElement('canvas');
          let width = img.width;
          let height = img.height;

          // Resize if too large (max dimension 1920px)
          const maxDimension = 1920;
          if (width > maxDimension || height > maxDimension) {
            if (width > height) {
              height = Math.round((height * maxDimension) / width);
              width = maxDimension;
            } else {
              width = Math.round((width * maxDimension) / height);
              height = maxDimension;
            }
          }

          canvas.width = width;
          canvas.height = height;

          const ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0, width, height);

          canvas.toBlob((blob) => {
            if (!blob) {
              reject(new Error('Compression failed'));
              return;
            }

            // Create new file
            const newFile = new File([blob], file.name, {
              type: 'image/jpeg',
              lastModified: Date.now()
            });

            console.log(`Compressed ${file.name}: ${(file.size / 1024 / 1024).toFixed(2)}MB -> ${(newFile.size / 1024 / 1024).toFixed(2)}MB`);
            resolve(newFile);
          }, 'image/jpeg', quality);
        };

        img.onerror = (error) => reject(error);
      };

      reader.onerror = (error) => reject(error);
    });
  },

  async processFiles(files) {
    const processedFiles = new DataTransfer();
    const promises = [];

    for (let i = 0; i < files.length; i++) {
      promises.push(this.compress(files[i]));
    }

    try {
      const results = await Promise.all(promises);
      results.forEach(file => processedFiles.items.add(file));
      return processedFiles.files;
    } catch (error) {
      console.error('Error processing files:', error);
      throw error;
    }
  }
};
