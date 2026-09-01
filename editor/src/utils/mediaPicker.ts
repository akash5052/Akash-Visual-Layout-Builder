export type MediaPickMeta = {
  alt: string;
  title: string;
  filename: string;
};

export type OpenImagePickerOptions = {
  title?: string;
  button?: string;
};

export function openImagePicker(
  onSelect: (url: string, id: number, meta?: MediaPickMeta) => void,
  options: OpenImagePickerOptions = {}
): boolean {
  if (!window.wp?.media) {
    return false;
  }

  const frame = window.wp.media({
    title: options.title || "Select image",
    button: { text: options.button || "Use image" },
    multiple: false,
    library: { type: "image" },
  });

  frame.on("select", () => {
    const attachment = frame.state().get("selection").first().toJSON() as {
      url?: string;
      id?: number;
      alt?: string;
      title?: string;
      filename?: string;
      name?: string;
    };
    if (attachment?.url) {
      onSelect(attachment.url, attachment.id ?? 0, {
        alt: attachment.alt || "",
        title: attachment.title || "",
        filename: attachment.filename || attachment.name || "",
      });
    }
  });

  frame.open();
  return true;
}
